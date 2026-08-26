<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\GuestMessage;
use App\Services\EventFeedbackRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class EventFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = Event::create(['name' => 'Inabuyer 2026', 'slug' => 'inabuyer-2026', 'is_active' => true]);
    }

    public function test_feedback_page_is_accessible_for_active_event(): void
    {
        $response = $this->get(route('events.feedback', ['event' => $this->event->slug]));

        $response->assertOk();
        $response->assertSee('Jabatan');
        $response->assertSee('Nomor yang bisa');
        $response->assertSee('Email');
        $response->assertSee('data-feedback-form', false);
        $response->assertSee('data-csrf-refresh-url', false);
        $response->assertSee(route('events.feedback.csrf-token', ['event' => $this->event->slug]), false);
        $response->assertSee('name="website"', false);
    }

    public function test_feedback_csrf_token_endpoint_returns_uncached_token_for_active_event(): void
    {
        $response = $this->getJson(route('events.feedback.csrf-token', ['event' => $this->event->slug]));

        $response->assertOk();
        $response->assertJsonStructure(['token']);

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertIsString($response->json('token'));
        $this->assertNotSame('', $response->json('token'));
    }

    public function test_inactive_event_returns_404_for_get_csrf_and_post(): void
    {
        $inactiveEvent = Event::create([
            'name' => 'Past Exhibition',
            'slug' => 'past-exhibition',
            'is_active' => false,
        ]);

        $this->get(route('events.feedback', ['event' => $inactiveEvent->slug]))
            ->assertNotFound();

        $this->getJson(route('events.feedback.csrf-token', ['event' => $inactiveEvent->slug]))
            ->assertNotFound();

        $payload = [
            'name' => 'Doni Kusuma',
            'organization' => 'PT Sumber Rejeki',
            'kesan_dan_pesan' => 'Mencoba submit ke event non-aktif.',
        ];

        $this->post(route('events.feedback.store', ['event' => $inactiveEvent->slug]), $payload)
            ->assertNotFound();

        $this->assertSame(0, GuestMessage::query()->count());
    }

    public function test_inactive_event_never_returns_429_even_when_exhausting_rate_limits(): void
    {
        $inactiveEvent = Event::create([
            'name' => 'Past Exhibition 2',
            'slug' => 'past-exhibition-2',
            'is_active' => false,
        ]);

        // Exhaust POST rate limit threshold (35 requests > 30 threshold)
        for ($i = 1; $i <= 35; $i++) {
            $payload = [
                'name' => "Visitor {$i}",
                'organization' => "PT Inactive {$i}",
                'email' => "visitor{$i}@inactive.example",
                'kesan_dan_pesan' => "Message {$i}",
            ];

            $response = $this->post(route('events.feedback.store', ['event' => $inactiveEvent->slug]), $payload);
            $response->assertNotFound();
            $this->assertNotSame(429, $response->getStatusCode());
        }

        $this->assertSame(0, GuestMessage::query()->count());

        // Exhaust CSRF rate limit threshold (65 requests > 60 threshold)
        for ($i = 1; $i <= 65; $i++) {
            $response = $this->getJson(route('events.feedback.csrf-token', ['event' => $inactiveEvent->slug]));
            $response->assertNotFound();
            $this->assertNotSame(429, $response->getStatusCode());
        }

        // Normal GET form requests also remain 404
        $this->get(route('events.feedback', ['event' => $inactiveEvent->slug]))
            ->assertNotFound();
    }

    public function test_rate_limiter_keys_do_not_contain_plaintext_pii_or_raw_ip(): void
    {
        $email = 'Aisyah@example.com';
        $phone = '+62 812 3456 7890';
        $ip = '192.168.1.100';
        $eventId = $this->event->id;

        $emailFingerprint = EventFeedbackRateLimiter::fingerprintContact([
            'email' => $email,
            'phone' => $phone,
        ]);
        $phoneFingerprint = EventFeedbackRateLimiter::fingerprintContact([
            'phone' => $phone,
        ]);
        $fallbackFingerprint = EventFeedbackRateLimiter::fingerprintContact([
            'name' => '  Aisyah Putri  ',
            'organization' => '  PT Nusantara  ',
        ]);

        $ipKey = EventFeedbackRateLimiter::ipKey($eventId, $ip);
        $csrfKey = EventFeedbackRateLimiter::csrfKey($eventId, $ip);
        $contactKey = EventFeedbackRateLimiter::contactKey($eventId, $emailFingerprint);

        // Ensure digests are valid SHA-256 strings
        $this->assertSame(64, strlen($emailFingerprint));
        $this->assertSame(64, strlen($phoneFingerprint));
        $this->assertSame(64, strlen($fallbackFingerprint));

        $this->assertSame(hash('sha256', 'email:aisyah@example.com'), $emailFingerprint);
        $this->assertSame(hash('sha256', 'phone:6281234567890'), $phoneFingerprint);
        $this->assertSame(hash('sha256', 'contact:Aisyah Putri:PT Nusantara'), $fallbackFingerprint);

        // Ensure keys do NOT contain plaintext PII or raw IP
        $this->assertStringNotContainsString('Aisyah@example.com', $contactKey);
        $this->assertStringNotContainsString('aisyah@example.com', $contactKey);
        $this->assertStringNotContainsString('+62 812 3456 7890', $contactKey);
        $this->assertStringNotContainsString('6281234567890', $contactKey);
        $this->assertStringNotContainsString('192.168.1.100', $ipKey);
        $this->assertStringNotContainsString('192.168.1.100', $csrfKey);

        // Ensure expected key pattern with SHA-256 digest
        $this->assertStringStartsWith("event-feedback:post:ip:{$eventId}:", $ipKey);
        $this->assertStringStartsWith("event-feedback:csrf:{$eventId}:", $csrfKey);
        $this->assertStringStartsWith("event-feedback:post:contact:{$eventId}:", $contactKey);
    }

    public function test_contact_normalization_maps_different_cases_and_phone_formats_to_same_bucket(): void
    {
        // Email casing normalization check
        $fpUpper = EventFeedbackRateLimiter::fingerprintContact(['email' => 'AISYAH@EXAMPLE.COM']);
        $fpLower = EventFeedbackRateLimiter::fingerprintContact(['email' => 'aisyah@example.com']);
        $this->assertSame($fpUpper, $fpLower);

        // Phone formatting normalization check
        $fpFormatted = EventFeedbackRateLimiter::fingerprintContact(['phone' => '+62 812-3456-7890']);
        $fpSpaced = EventFeedbackRateLimiter::fingerprintContact(['phone' => '+62 812 3456 7890']);
        $this->assertSame($fpFormatted, $fpSpaced);

        // Rate limiter bucket consumption test: Upper and lower case emails hit same bucket
        RateLimiter::clear(EventFeedbackRateLimiter::contactKey($this->event->id, $fpUpper));
        RateLimiter::clear(EventFeedbackRateLimiter::ipKey($this->event->id, '127.0.0.1'));

        for ($i = 1; $i <= 3; $i++) {
            $response = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), [
                'name' => 'Aisyah Putri',
                'organization' => 'PT Nusantara',
                'email' => 'AISYAH@EXAMPLE.COM',
                'kesan_dan_pesan' => "Pesan unik ke-{$i}",
            ]);
            $response->assertStatus(302);
        }

        // 4th submission with lowercase email is throttled
        $throttledResponse = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), [
            'name' => 'Aisyah Putri',
            'organization' => 'PT Nusantara',
            'email' => 'aisyah@example.com',
            'kesan_dan_pesan' => 'Pesan unik ke-4',
        ]);
        $throttledResponse->assertStatus(429);
    }

    public function test_contact_limiter_preserves_event_isolation(): void
    {
        $eventA = $this->event;
        $eventB = Event::create(['name' => 'Hospital Expo 2026', 'slug' => 'hospital-expo-2026', 'is_active' => true]);

        $email = 'isolated.visitor@example.com';
        $contactFingerprint = EventFeedbackRateLimiter::fingerprintContact(['email' => $email]);

        RateLimiter::clear(EventFeedbackRateLimiter::contactKey($eventA->id, $contactFingerprint));
        RateLimiter::clear(EventFeedbackRateLimiter::contactKey($eventB->id, $contactFingerprint));
        RateLimiter::clear(EventFeedbackRateLimiter::ipKey($eventA->id, '127.0.0.1'));
        RateLimiter::clear(EventFeedbackRateLimiter::ipKey($eventB->id, '127.0.0.1'));

        // Exhaust contact limit on Event A (3 submissions)
        for ($i = 1; $i <= 3; $i++) {
            $this->post(route('events.feedback.store', ['event' => $eventA->slug]), [
                'name' => 'Isolated Visitor',
                'organization' => 'PT Isolated',
                'email' => $email,
                'kesan_dan_pesan' => "Event A distinct message {$i}",
            ])->assertStatus(302);
        }

        // 4th submission on Event A is throttled
        $this->post(route('events.feedback.store', ['event' => $eventA->slug]), [
            'name' => 'Isolated Visitor',
            'organization' => 'PT Isolated',
            'email' => $email,
            'kesan_dan_pesan' => 'Event A distinct message 4',
        ])->assertStatus(429);

        // 1st submission on Event B for SAME contact succeeds and is NOT throttled
        $this->post(route('events.feedback.store', ['event' => $eventB->slug]), [
            'name' => 'Isolated Visitor',
            'organization' => 'PT Isolated',
            'email' => $email,
            'kesan_dan_pesan' => 'Event B distinct message 1',
        ])->assertStatus(302);

        $this->assertSame(3, $eventA->guestMessages()->count());
        $this->assertSame(1, $eventB->guestMessages()->count());
    }

    public function test_feedback_submission_is_stored_successfully(): void
    {
        $payload = [
            'name' => 'Aisyah Putri',
            'organization' => 'PT Nusantara Export',
            'position' => 'Business Development Manager',
            'phone' => '+62 812 3456 7890',
            'email' => 'aisyah.putri@example.com',
            'kesan_dan_pesan' => 'Acara sangat bermanfaat dan saya berharap sesi networking ditambah pada tahun berikutnya.',
        ];

        $response = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $payload);

        $response->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guest_messages', [
            'event_id' => $this->event->id,
            'name' => 'Aisyah Putri',
            'organization' => 'PT Nusantara Export',
            'position' => 'Business Development Manager',
            'phone' => '+62 812 3456 7890',
            'email' => 'aisyah.putri@example.com',
            'kesan_dan_pesan' => 'Acara sangat bermanfaat dan saya berharap sesi networking ditambah pada tahun berikutnya.',
            'is_visible' => true,
        ]);
    }

    public function test_honeypot_silently_discards_submission_without_creating_guest_message(): void
    {
        $spamPayload = [
            'name' => 'Spam Bot',
            'organization' => 'Bot Net Ltd',
            'email' => 'bot@spammer.example',
            'kesan_dan_pesan' => 'Buy cheap meds at https://spam.example',
            'website' => 'https://spam.example',
        ];

        $response = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $spamPayload);

        $response->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));
        $response->assertSessionHas('success');
        $response->assertSessionDoesntHaveErrors();
        $this->assertSame(0, GuestMessage::query()->count());

        $humanPayload = [
            'name' => 'Human Visitor',
            'organization' => 'RS Harapan',
            'email' => 'human@harapan.example',
            'kesan_dan_pesan' => 'Booth sangat rapi dan informatif.',
            'website' => '',
        ];

        $response = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $humanPayload);

        $response->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));
        $response->assertSessionHas('success');
        $this->assertSame(1, GuestMessage::query()->count());
    }

    public function test_duplicate_submission_within_window_is_suppressed_for_same_event(): void
    {
        $payload = [
            'name' => 'Citra Dewi',
            'organization' => 'PT Medika Utama',
            'position' => 'Procurement Officer',
            'phone' => '081299998888',
            'email' => 'citra@medika.example',
            'kesan_dan_pesan' => 'Demo alat rontgen digital sangat impresif!',
        ];

        $firstResponse = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $payload);
        $firstResponse->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));
        $firstResponse->assertSessionHas('success');
        $this->assertSame(1, GuestMessage::query()->count());

        // Immediate repeat submission with identical payload
        $secondResponse = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $payload);
        $secondResponse->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));
        $secondResponse->assertSessionHas('success');
        $this->assertSame(1, GuestMessage::query()->count());

        // Submission with modified message text is allowed as distinct row
        $modifiedPayload = array_merge($payload, [
            'kesan_dan_pesan' => 'Tambahan catatan: kami berminat untuk demo di rumah sakit kami.',
        ]);

        $thirdResponse = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $modifiedPayload);
        $thirdResponse->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));
        $thirdResponse->assertSessionHas('success');
        $this->assertSame(2, GuestMessage::query()->count());
    }

    public function test_input_normalization_collapses_whitespace_and_normalizes_email(): void
    {
        $firstPayload = [
            'name' => '  Aisyah Putri  ',
            'organization' => '  PT   Nusantara   Export  ',
            'position' => '  Business   Development  ',
            'phone' => '  +62 812 3456 7890  ',
            'email' => 'AISYAH@EXAMPLE.COM',
            'kesan_dan_pesan' => 'Acara   sangat   bagus',
        ];

        $response = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $firstPayload);
        $response->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));

        $message = GuestMessage::query()->firstOrFail();
        $this->assertSame('Aisyah Putri', $message->name);
        $this->assertSame('PT Nusantara Export', $message->organization);
        $this->assertSame('Business Development', $message->position);
        $this->assertSame('+62 812 3456 7890', $message->phone);
        $this->assertSame('aisyah@example.com', $message->email);
        $this->assertSame('Acara sangat bagus', $message->kesan_dan_pesan);

        // Second submission with pre-normalized values should be recognized as duplicate
        $secondPayload = [
            'name' => 'Aisyah Putri',
            'organization' => 'PT Nusantara Export',
            'position' => 'Business Development',
            'phone' => '+62 812 3456 7890',
            'email' => 'aisyah@example.com',
            'kesan_dan_pesan' => 'Acara sangat bagus',
        ];

        $response2 = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $secondPayload);
        $response2->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));
        $this->assertSame(1, GuestMessage::query()->count());
    }

    public function test_unicode_and_indonesian_characters_are_preserved(): void
    {
        $payload = [
            'name' => 'Dr. Bambang Tri-Atmojo, Sp.Rad & Rekan',
            'organization' => 'RSUD dr. Soetomo — Surabaya',
            'position' => 'Kepala Instalasi Radiologi',
            'phone' => '(031) 555-1234',
            'email' => 'bambang.rad@soetomo.example',
            'kesan_dan_pesan' => 'Inovasi CCXD buatan UGM & PT Madeena sangat membanggakan! Sukses selalu untuk karya anak bangsa.',
        ];

        $response = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $payload);
        $response->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));

        $message = GuestMessage::query()->firstOrFail();
        $this->assertSame('Dr. Bambang Tri-Atmojo, Sp.Rad & Rekan', $message->name);
        $this->assertSame('RSUD dr. Soetomo — Surabaya', $message->organization);
        $this->assertSame('Kepala Instalasi Radiologi', $message->position);
        $this->assertSame('Inovasi CCXD buatan UGM & PT Madeena sangat membanggakan! Sukses selalu untuk karya anak bangsa.', $message->kesan_dan_pesan);
    }

    public function test_duplicate_suppression_is_scoped_by_event(): void
    {
        $eventA = $this->event;
        $eventB = Event::create(['name' => 'Hospital Expo 2026', 'slug' => 'hospital-expo-2026', 'is_active' => true]);

        $payload = [
            'name' => 'Aisyah Putri',
            'organization' => 'PT Nusantara Export',
            'kesan_dan_pesan' => 'Selamat atas pamerannya!',
        ];

        $this->post(route('events.feedback.store', ['event' => $eventA->slug]), $payload)
            ->assertRedirect(route('events.feedback', ['event' => $eventA->slug]));

        $this->post(route('events.feedback.store', ['event' => $eventB->slug]), $payload)
            ->assertRedirect(route('events.feedback', ['event' => $eventB->slug]));

        $this->assertSame(1, $eventA->guestMessages()->count());
        $this->assertSame(1, $eventB->guestMessages()->count());
        $this->assertSame(2, GuestMessage::query()->count());
    }

    public function test_validation_rejects_missing_required_fields_and_invalid_email_and_overlength(): void
    {
        // Missing kesan_dan_pesan
        $payloadMissing = [
            'name' => 'Bima Arta',
            'organization' => 'PT Mitra Dagang',
            'kesan_dan_pesan' => '',
        ];

        $response = $this->from(route('events.feedback', ['event' => $this->event->slug]))
            ->post(route('events.feedback.store', ['event' => $this->event->slug]), $payloadMissing);

        $response->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));
        $response->assertSessionHasErrors(['kesan_dan_pesan']);
        $this->assertSame(0, GuestMessage::query()->count());

        // Invalid email format
        $payloadInvalidEmail = [
            'name' => 'Bima Arta',
            'organization' => 'PT Mitra Dagang',
            'email' => 'bima-not-an-email',
            'kesan_dan_pesan' => 'Pesan valid.',
        ];

        $response = $this->from(route('events.feedback', ['event' => $this->event->slug]))
            ->post(route('events.feedback.store', ['event' => $this->event->slug]), $payloadInvalidEmail);

        $response->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));
        $response->assertSessionHasErrors(['email']);
        $this->assertSame(0, GuestMessage::query()->count());

        // Overlength message (>5000 chars)
        $payloadOverLength = [
            'name' => 'Bima Arta',
            'organization' => 'PT Mitra Dagang',
            'kesan_dan_pesan' => str_repeat('A', 5001),
        ];

        $response = $this->from(route('events.feedback', ['event' => $this->event->slug]))
            ->post(route('events.feedback.store', ['event' => $this->event->slug]), $payloadOverLength);

        $response->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));
        $response->assertSessionHasErrors(['kesan_dan_pesan']);
        $this->assertSame(0, GuestMessage::query()->count());
    }

    public function test_rate_limiter_throttles_excessive_post_submissions_by_ip(): void
    {
        RateLimiter::clear(EventFeedbackRateLimiter::ipKey($this->event->id, '127.0.0.1'));

        // 30 requests with distinct contact fingerprints permitted under 30/min IP limit
        for ($i = 1; $i <= 30; $i++) {
            $payload = [
                'name' => "Rate Limit Test User {$i}",
                'organization' => "PT Benchmark {$i}",
                'email' => "user{$i}@benchmark.example",
                'kesan_dan_pesan' => "Pesan pengujian rate limiter unique {$i}",
            ];

            $response = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $payload);
            $response->assertStatus(302);
        }

        $this->assertSame(30, GuestMessage::query()->count());

        // 31st request from same IP exceeds the 30/minute threshold -> 429 Too Many Requests
        $overflowPayload = [
            'name' => 'Rate Limit Test User 31',
            'organization' => 'PT Benchmark 31',
            'email' => 'user31@benchmark.example',
            'kesan_dan_pesan' => 'Pesan overflow melebihi limit',
        ];

        $blockedResponse = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $overflowPayload);
        $blockedResponse->assertStatus(429);

        // Ensure no extra DB record was created after throttling
        $this->assertSame(30, GuestMessage::query()->count());
    }

    public function test_rate_limiter_throttles_excessive_post_submissions_by_contact(): void
    {
        $contactEmail = 'frequent.sender@example.com';
        $contactFingerprint = EventFeedbackRateLimiter::fingerprintContact(['email' => $contactEmail]);

        RateLimiter::clear(EventFeedbackRateLimiter::contactKey($this->event->id, $contactFingerprint));
        RateLimiter::clear(EventFeedbackRateLimiter::ipKey($this->event->id, '127.0.0.1'));

        // 3 submissions with different messages permitted under 3/10min contact limit
        for ($i = 1; $i <= 3; $i++) {
            $payload = [
                'name' => 'Frequent Sender',
                'organization' => 'PT Frequent',
                'email' => $contactEmail,
                'kesan_dan_pesan' => "Distinct message from frequent sender {$i}",
            ];

            $response = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $payload);
            $response->assertStatus(302);
        }

        $this->assertSame(3, GuestMessage::query()->count());

        // 4th submission within window for same contact is throttled with 429
        $overflowPayload = [
            'name' => 'Frequent Sender',
            'organization' => 'PT Frequent',
            'email' => $contactEmail,
            'kesan_dan_pesan' => '4th distinct message from frequent sender',
        ];

        $blockedResponse = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), $overflowPayload);
        $blockedResponse->assertStatus(429);

        $this->assertSame(3, GuestMessage::query()->count());
    }

    public function test_rate_limiter_throttles_excessive_csrf_token_requests(): void
    {
        RateLimiter::clear(EventFeedbackRateLimiter::csrfKey($this->event->id, '127.0.0.1'));

        for ($i = 1; $i <= 60; $i++) {
            $response = $this->getJson(route('events.feedback.csrf-token', ['event' => $this->event->slug]));
            $response->assertOk();
        }

        $blockedResponse = $this->getJson(route('events.feedback.csrf-token', ['event' => $this->event->slug]));
        $blockedResponse->assertStatus(429);
    }

    public function test_shared_ip_booth_allows_multiple_distinct_visitors_below_threshold(): void
    {
        RateLimiter::clear(EventFeedbackRateLimiter::ipKey($this->event->id, '127.0.0.1'));

        $visitors = [
            ['name' => 'Pengunjung 1', 'email' => 'p1@example.com', 'org' => 'RS A', 'msg' => 'Pesan pengunjung 1'],
            ['name' => 'Pengunjung 2', 'email' => 'p2@example.com', 'org' => 'RS B', 'msg' => 'Pesan pengunjung 2'],
            ['name' => 'Pengunjung 3', 'email' => 'p3@example.com', 'org' => 'RS C', 'msg' => 'Pesan pengunjung 3'],
            ['name' => 'Pengunjung 4', 'email' => 'p4@example.com', 'org' => 'RS D', 'msg' => 'Pesan pengunjung 4'],
            ['name' => 'Pengunjung 5', 'email' => 'p5@example.com', 'org' => 'RS E', 'msg' => 'Pesan pengunjung 5'],
        ];

        foreach ($visitors as $v) {
            $response = $this->post(route('events.feedback.store', ['event' => $this->event->slug]), [
                'name' => $v['name'],
                'organization' => $v['org'],
                'email' => $v['email'],
                'kesan_dan_pesan' => $v['msg'],
            ]);

            $response->assertRedirect(route('events.feedback', ['event' => $this->event->slug]));
            $response->assertSessionHas('success');
        }

        $this->assertSame(5, GuestMessage::query()->count());
    }

    public function test_feedback_page_preserves_indonesian_locale_under_ambient_english(): void
    {
        app()->setLocale('en');
        config(['app.locale' => 'en']);

        $response = $this->get(route('events.feedback', ['event' => $this->event->slug]));

        $response->assertOk();
        $response->assertSee('lang="id"', false);
        $response->assertSee('Navigasi');
        $response->assertSee('Kontak');
        $response->assertSee('Seluruh hak dilindungi.');
        $response->assertSee('Kesan dan Pesan untuk '.$this->event->name);
        $response->assertSee('Form Feedback');
        $response->assertDontSee('data-testid="language-switcher-desktop"', false);
        $response->assertDontSee('data-testid="language-switcher-mobile"', false);
    }

    public function test_feedback_page_renders_event_specific_identity_and_does_not_leak_legacy_inabuyer_presentation(): void
    {
        $customEvent = Event::create([
            'name' => 'Rumah Skrining Prestige',
            'slug' => 'rumah-skrining-prestige',
            'is_active' => true,
        ]);

        $response = $this->get(route('events.feedback', ['event' => $customEvent->slug]));

        $response->assertOk();
        $response->assertSee('Rumah Skrining Prestige');
        $response->assertSee('Feedback — Rumah Skrining Prestige');
        $response->assertSee('Kesan dan Pesan untuk Rumah Skrining Prestige');
        $response->assertSee('Ceritakan pengalaman, masukan, atau harapan Anda untuk Rumah Skrining Prestige');
        $response->assertSee('Form Feedback');
        $response->assertSee('Kirim Feedback');
        $response->assertSee(route('events.feedback.store', ['event' => $customEvent->slug]), false);
        $response->assertSee(route('events.feedback.csrf-token', ['event' => $customEvent->slug]), false);

        // Ensure legacy Inabuyer presentation is not leaked
        $response->assertDontSee('Inabuyer 2026');
        $response->assertDontSee('Booth Madeena Inabuyer 2026');
        $response->assertDontSee('https://bit.ly/madeenafeedback');
        $response->assertDontSee('qr_Kesan dan Pesan Booth Madeena Inabuyer 2026.png');
    }

    public function test_feedback_page_renders_legacy_inabuyer_event_via_model_name(): void
    {
        $inabuyerEvent = Event::create([
            'name' => 'Inabuyer 2026',
            'slug' => 'inabuyer-2026-event',
            'is_active' => true,
        ]);

        $response = $this->get(route('events.feedback', ['event' => $inabuyerEvent->slug]));

        $response->assertOk();
        $response->assertSee('Inabuyer 2026');
        $response->assertSee('Feedback — Inabuyer 2026');
        $response->assertSee('Kesan dan Pesan untuk Inabuyer 2026');
        $response->assertSee('Ceritakan pengalaman, masukan, atau harapan Anda untuk Inabuyer 2026');
        $response->assertSee('Kirim Feedback');
    }
}

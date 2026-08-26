<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\GuestMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_shows_only_visible_messages_and_feedback_cta(): void
    {
        $event = Event::create(['name' => 'Rumah Skrining Prestige', 'slug' => 'rumah-skrining-prestige', 'is_active' => true]);

        GuestMessage::query()->create([
            'event_id' => $event->id,
            'name' => 'Visible Guest',
            'organization' => 'PT Terbuka',
            'kesan_dan_pesan' => 'Pesan ini harus tampil di display.',
            'is_visible' => true,
        ]);

        GuestMessage::query()->create([
            'event_id' => $event->id,
            'name' => 'Hidden Guest',
            'organization' => 'PT Disembunyikan',
            'kesan_dan_pesan' => 'Pesan ini tidak boleh tampil di display.',
            'is_visible' => false,
        ]);

        $response = $this->get(route('events.display', ['event' => $event->slug]));

        $response->assertOk();
        $response->assertSee('Visible Guest');
        $response->assertSee('Pesan ini harus tampil di display.');
        $response->assertDontSee('Hidden Guest');
        $response->assertDontSee('Pesan ini tidak boleh tampil di display.');
        $response->assertSee('Rumah Skrining Prestige');
        $response->assertSee(route('events.feedback', ['event' => 'rumah-skrining-prestige']));
        $response->assertDontSee('https://bit.ly/madeenafeedback');
        $response->assertDontSee('qr_Kesan dan Pesan Booth Madeena Inabuyer 2026.png');
        $response->assertDontSee('Inabuyer 2026');
        $response->assertSee('<title>Rumah Skrining Prestige — Live Display | PT Madeena Karya Indonesia</title>', false);
    }

    public function test_display_escapes_malicious_user_input_against_xss(): void
    {
        $event = Event::create(['name' => 'Inabuyer 2026', 'slug' => 'inabuyer-2026', 'is_active' => true]);

        GuestMessage::query()->create([
            'event_id' => $event->id,
            'name' => '<script>alert("xss-name")</script>',
            'organization' => '<img src=x onerror=alert("xss-org")>',
            'kesan_dan_pesan' => '<script>alert("xss-message")</script>',
            'is_visible' => true,
        ]);

        $response = $this->get(route('events.display', ['event' => $event->slug]));

        $response->assertOk();

        // Ensure raw executable HTML tags are NOT rendered
        $response->assertDontSee('<script>alert("xss-name")</script>', false);
        $response->assertDontSee('<img src=x onerror=alert("xss-org")>', false);
        $response->assertDontSee('<script>alert("xss-message")</script>', false);

        // Ensure proper HTML entity escaping is present
        $response->assertSee(e('<script>alert("xss-name")</script>'), false);
        $response->assertSee(e('<img src=x onerror=alert("xss-org")>'), false);
        $response->assertSee(e('<script>alert("xss-message")</script>'), false);
    }

    public function test_display_browser_title_is_event_aware(): void
    {
        $event = Event::create([
            'name' => 'Rumah Skrining Prestige',
            'slug' => 'rumah-skrining-prestige',
            'is_active' => true,
        ]);

        $response = $this->get(route('events.display', ['event' => $event->slug]));

        $response->assertOk();
        $response->assertSee('<title>Rumah Skrining Prestige — Live Display | PT Madeena Karya Indonesia</title>', false);
    }

    public function test_display_renders_legacy_inabuyer_event_without_hardcoded_bitly_or_static_qr(): void
    {
        $event = Event::create([
            'name' => 'Inabuyer 2026',
            'slug' => 'inabuyer-2026',
            'is_active' => true,
        ]);

        $response = $this->get(route('events.display', ['event' => $event->slug]));

        $response->assertOk();
        $response->assertSee('Inabuyer 2026');
        $response->assertSee(route('events.feedback', ['event' => 'inabuyer-2026']));
        $response->assertDontSee('https://bit.ly/madeenafeedback');
        $response->assertDontSee('qr_Kesan dan Pesan Booth Madeena Inabuyer 2026.png');
        $response->assertSee('<title>Inabuyer 2026 — Live Display | PT Madeena Karya Indonesia</title>', false);
    }
}

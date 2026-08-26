<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\GuestMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function create(Event $event): View
    {
        $this->ensureEventIsActive($event);

        app()->setLocale('id');
        $locale = 'id';
        $showLanguageSwitcher = false;

        return view('event.feedback', compact('event', 'locale', 'showLanguageSwitcher'));
    }

    public function csrfToken(Event $event): JsonResponse
    {
        $this->ensureEventIsActive($event);

        return response()
            ->json(['token' => csrf_token()])
            ->header('Cache-Control', 'no-store');
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $this->ensureEventIsActive($event);

        // Honeypot check: silently accept and discard bot submissions
        if (trim((string) $request->input('website', '')) !== '') {
            return redirect()
                ->route('events.feedback', ['event' => $event->slug])
                ->with('success', 'Terima kasih. Kesan dan pesan Anda telah kami terima.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'kesan_dan_pesan' => ['required', 'string', 'max:5000'],
        ]);

        $normalized = $this->normalizeFeedbackInput($validated);

        if ($this->isDuplicateSubmission($event, $normalized)) {
            return redirect()
                ->route('events.feedback', ['event' => $event->slug])
                ->with('success', 'Terima kasih. Kesan dan pesan Anda telah kami terima.');
        }

        GuestMessage::create([
            'event_id' => $event->id,
            'name' => $normalized['name'],
            'organization' => $normalized['organization'],
            'position' => $normalized['position'],
            'phone' => $normalized['phone'],
            'email' => $normalized['email'],
            'kesan_dan_pesan' => $normalized['kesan_dan_pesan'],
        ]);

        return redirect()
            ->route('events.feedback', ['event' => $event->slug])
            ->with('success', 'Terima kasih. Kesan dan pesan Anda telah kami terima.');
    }

    private function ensureEventIsActive(Event $event): void
    {
        abort_unless($event->is_active, 404);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{name: string, organization: string, position: ?string, phone: ?string, email: ?string, kesan_dan_pesan: string}
     */
    private function normalizeFeedbackInput(array $data): array
    {
        $normalizeText = static function (?string $value): string {
            return trim(preg_replace('/\s+/u', ' ', (string) $value));
        };

        $name = $normalizeText($data['name'] ?? '');
        $organization = $normalizeText($data['organization'] ?? '');

        $position = isset($data['position']) ? $normalizeText($data['position']) : '';
        $position = $position !== '' ? $position : null;

        $phone = isset($data['phone']) ? $normalizeText($data['phone']) : '';
        $phone = $phone !== '' ? $phone : null;

        $email = isset($data['email']) ? strtolower(trim((string) $data['email'])) : '';
        $email = $email !== '' ? $email : null;

        $kesanDanPesan = $normalizeText($data['kesan_dan_pesan'] ?? '');

        return [
            'name' => $name,
            'organization' => $organization,
            'position' => $position,
            'phone' => $phone,
            'email' => $email,
            'kesan_dan_pesan' => $kesanDanPesan,
        ];
    }

    /**
     * @param  array{name: string, organization: string, position: ?string, phone: ?string, email: ?string, kesan_dan_pesan: string}  $normalized
     */
    private function isDuplicateSubmission(Event $event, array $normalized): bool
    {
        $query = GuestMessage::query()
            ->where('event_id', $event->id)
            ->where('name', $normalized['name'])
            ->where('organization', $normalized['organization'])
            ->where('kesan_dan_pesan', $normalized['kesan_dan_pesan'])
            ->where('created_at', '>=', now()->subMinutes(2));

        if ($normalized['position'] !== null) {
            $query->where('position', $normalized['position']);
        } else {
            $query->whereNull('position');
        }

        if ($normalized['phone'] !== null) {
            $query->where('phone', $normalized['phone']);
        } else {
            $query->whereNull('phone');
        }

        if ($normalized['email'] !== null) {
            $query->where('email', $normalized['email']);
        } else {
            $query->whereNull('email');
        }

        return $query->exists();
    }
}

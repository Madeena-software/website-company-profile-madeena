<?php

namespace App\Services;

use App\Models\Event as EventModel;
use Illuminate\Http\Request;

class EventFeedbackRateLimiter
{
    public static function resolveEvent(Request $request): ?EventModel
    {
        $event = $request->route('event');
        if ($event instanceof EventModel) {
            return $event;
        }

        if (! is_string($event) || trim($event) === '') {
            return null;
        }

        $attributeKey = '_resolved_event_model:'.$event;
        if ($request->attributes->has($attributeKey)) {
            return $request->attributes->get($attributeKey);
        }

        $model = EventModel::query()->where('slug', $event)->first();
        $request->attributes->set($attributeKey, $model);

        return $model;
    }

    public static function fingerprintIp(string $ip): string
    {
        return hash('sha256', trim($ip));
    }

    /**
     * @param  Request|array<string, mixed>  $input
     */
    public static function fingerprintContact(Request|array $input): ?string
    {
        $email = is_array($input) ? ($input['email'] ?? null) : $input->input('email');
        $phone = is_array($input) ? ($input['phone'] ?? null) : $input->input('phone');
        $name = is_array($input) ? ($input['name'] ?? null) : $input->input('name');
        $org = is_array($input) ? ($input['organization'] ?? null) : $input->input('organization');

        $normalizedEmail = strtolower(trim((string) $email));
        $normalizedPhone = trim(preg_replace('/\D+/', '', (string) $phone));
        $normalizedName = trim(preg_replace('/\s+/u', ' ', (string) $name));
        $normalizedOrg = trim(preg_replace('/\s+/u', ' ', (string) $org));

        $canonical = null;
        if ($normalizedEmail !== '') {
            $canonical = "email:{$normalizedEmail}";
        } elseif ($normalizedPhone !== '') {
            $canonical = "phone:{$normalizedPhone}";
        } elseif ($normalizedName !== '' || $normalizedOrg !== '') {
            $canonical = "contact:{$normalizedName}:{$normalizedOrg}";
        }

        if ($canonical === null) {
            return null;
        }

        return hash('sha256', $canonical);
    }

    public static function ipKey(string|int $eventId, string $ip): string
    {
        $digest = self::fingerprintIp($ip);

        return "event-feedback:post:ip:{$eventId}:{$digest}";
    }

    public static function csrfKey(string|int $eventId, string $ip): string
    {
        $digest = self::fingerprintIp($ip);

        return "event-feedback:csrf:{$eventId}:{$digest}";
    }

    public static function contactKey(string|int $eventId, string $contactFingerprint): string
    {
        return "event-feedback:post:contact:{$eventId}:{$contactFingerprint}";
    }
}

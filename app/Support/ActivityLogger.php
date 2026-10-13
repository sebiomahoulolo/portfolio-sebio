<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Journalise les actions administratives importantes (publication,
 * suppression, connexion...). L'adresse IP n'est conservée que hachée.
 */
class ActivityLogger
{
    public static function log(string $action, string $description, ?Model $subject = null, array $properties = []): ActivityLog
    {
        $request = request();

        return ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => mb_substr($description, 0, 255),
            'properties' => $properties ?: null,
            'ip_hash' => $request?->ip() ? hash_hmac('sha256', $request->ip(), config('app.key')) : null,
        ]);
    }
}

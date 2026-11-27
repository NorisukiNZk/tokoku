<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ActivityLogger
{
    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     */
    public static function record(
        string $action,
        string $subjectType,
        ?int $subjectId,
        string $subjectLabel,
        string $description,
        ?array $before = null,
        ?array $after = null,
        ?string $customActorName = null,
    ): void {
        try {
            if (! Schema::hasTable('activity_logs')) {
                return;
            }

            $user = Auth::user();
            $actor = $customActorName
                ?? ($user ? ($user->name . ' (' . $user->role . ')') : 'Sistem');

            ActivityLog::query()->create([
                'user_id' => $customActorName ? null : $user?->id,
                'actor_name' => $actor,
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'subject_label' => mb_substr($subjectLabel, 0, 180),
                'description' => $description,
                'before_values' => $before ?: null,
                'after_values' => $after ?: null,
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // Audit logging must never break primary workshop/store operations.
        }
    }
}

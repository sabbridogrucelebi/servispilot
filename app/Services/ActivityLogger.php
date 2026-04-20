<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(
        string $module,
        string $action,
        ?object $subject,
        string $title,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $meta = null
    ): void {
        $user = auth()->user();

        ActivityLog::create([
            'company_id' => $user?->company_id,
            'user_id' => $user?->id,
            'module' => $module,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject->id ?? null,
            'title' => $title,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'meta' => $meta,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
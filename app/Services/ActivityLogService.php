<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    /**
     * Şirkete ait logları sayfalı ve güvenli şekilde getirir.
     */
    public function getLogsPaginated($companyId, $perPage = 20)
    {
        // Maksimum limit koruması
        $perPage = min((int) $perPage, 50);
        if ($perPage < 1) $perPage = 20;

        $logs = ActivityLog::where('company_id', $companyId)
            ->with('user:id,name') // Sadece id ve name çekilir, şifre gibi alanlar gelmez
            ->select('id', 'company_id', 'user_id', 'module', 'action', 'title', 'description', 'ip_address', 'created_at')
            ->latest('created_at') // Newest first
            ->paginate($perPage);

        // API'de gösterilmek üzere manipüle ediliyor
        $logs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'title' => $log->title,
                'description' => $log->description,
                'module' => $log->module,
                'action' => $log->action,
                'user_name' => $log->user ? $log->user->name : 'Sistem',
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : null,
                'created_at_human' => $log->created_at ? tap($log->created_at, fn($dt) => \Carbon\Carbon::setLocale('tr'))->diffForHumans() : null,
            ];
        });

        return $logs;
    }
}

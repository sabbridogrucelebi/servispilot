<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    /**
     * Şirkete ait logları sayfalı ve güvenli şekilde getirir.
     */
    public function getLogsPaginated($companyId, $filters = [], $perPage = 20)
    {
        // Maksimum limit koruması
        $perPage = min((int) $perPage, 50);
        if ($perPage < 1) $perPage = 20;

        $logs = ActivityLog::where('company_id', $companyId)
            ->with('user:id,name') // Sadece id ve name çekilir, şifre gibi alanlar gelmez
            ->when(isset($filters['module']), fn ($q) => $q->where('module', $filters['module']))
            ->when(isset($filters['action']), fn ($q) => $q->where('action', $filters['action']))
            ->when(isset($filters['user_id']), fn ($q) => $q->where('user_id', $filters['user_id']))
            ->when(isset($filters['from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when(isset($filters['to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->when(isset($filters['search']), function ($q) use ($filters) {
                $needle = '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['search']) . '%';
                $q->where(function ($qq) use ($needle) {
                    $qq->where('title', 'like', $needle)
                       ->orWhere('description', 'like', $needle)
                       ->orWhere('ip_address', 'like', $needle);
                });
            })
            ->select('id', 'company_id', 'user_id', 'module', 'action', 'title', 'description', 'ip_address', 'created_at')
            ->latest('created_at') // Newest first
            ->paginate($perPage)
            ->withQueryString();

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyModule extends Model
{
    protected $fillable = [
        'company_id',
        'module_key',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | MODULE KEYS — Sistemdeki tüm modüller
    |--------------------------------------------------------------------------
    */

    public const ALL_MODULES = [
        'vehicles'          => 'Araçlar',
        'drivers'           => 'Personeller',
        'customers'         => 'Müşteriler',
        'service_routes'    => 'Servis Hatları',
        'route_stops'       => 'Duraklar',
        'trips'             => 'Puantaj / Sefer',
        'payrolls'          => 'Maaşlar',
        'documents'         => 'Belgeler',
        'fuels'             => 'Yakıt',
        'maintenances'      => 'Bakım / Tamir',
        'traffic_penalties' => 'Trafik Cezaları',
        'reports'           => 'Raporlar',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isExpired(): bool
    {
        if (is_null($this->expires_at)) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function isAvailable(): bool
    {
        return $this->is_active && !$this->isExpired();
    }
}

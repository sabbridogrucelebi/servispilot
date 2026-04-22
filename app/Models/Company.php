<?php

namespace App\Models;

use App\Models\Fleet\Driver;
use App\Models\Fleet\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'phone',
        'email',
        'tax_no',
        'city',
        'address',
        'is_active',
        'license_type',
        'license_expires_at',
        'max_vehicles',
        'max_users',
        'logo_path',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'license_expires_at' => 'datetime',
        'max_vehicles'       => 'integer',
        'max_users'          => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function serviceRoutes(): HasMany
    {
        return $this->hasMany(ServiceRoute::class);
    }

    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function fuels(): HasMany
    {
        return $this->hasMany(Fuel::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CompanyModule::class);
    }

    /*
    |--------------------------------------------------------------------------
    | LICENSE HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Lisans aktif mi kontrol eder.
     */
    public function isLicenseActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // license_expires_at null ise süresiz lisans
        if (is_null($this->license_expires_at)) {
            return true;
        }

        return $this->license_expires_at->isFuture();
    }

    /**
     * Lisans süresi kaç gün kaldı?
     */
    public function licenseDaysRemaining(): ?int
    {
        if (is_null($this->license_expires_at)) {
            return null; // süresiz
        }

        $days = (int) now()->startOfDay()->diffInDays($this->license_expires_at->startOfDay(), false);

        return max(0, $days);
    }

    /*
    |--------------------------------------------------------------------------
    | MODULE HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Firma bu modüle erişebilir mi?
     */
    public function hasModule(string $moduleKey): bool
    {
        if (!$this->relationLoaded('modules')) {
            $this->load('modules');
        }

        $module = $this->modules->firstWhere('module_key', $moduleKey);

        if (!$module) {
            return false;
        }

        return $module->isAvailable();
    }

    /**
     * Firmaya tüm modülleri aktif olarak ata.
     */
    public function activateAllModules(): void
    {
        foreach (CompanyModule::ALL_MODULES as $key => $label) {
            $this->modules()->updateOrCreate(
                ['module_key' => $key],
                ['is_active' => true, 'expires_at' => null]
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | QUOTA HELPERS
    |--------------------------------------------------------------------------
    */

    public function canAddVehicle(): bool
    {
        return $this->vehicles()->count() < $this->max_vehicles;
    }

    public function canAddUser(): bool
    {
        return $this->users()->count() < $this->max_users;
    }
}
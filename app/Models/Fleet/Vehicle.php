<?php

namespace App\Models\Fleet;

use App\Models\Fuel;
use App\Models\Company;
use App\Models\Document;
use App\Models\TrafficPenalty;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\VehicleMaintenance;
use App\Models\VehicleMaintenanceSetting;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'plate',
        'brand',
        'model',
        'vehicle_type',
        'vehicle_package',
        'model_year',
        'registration_date',
        'seat_count',
        'gear_type',
        'fuel_type',
        'color',
        'other_color',
        'engine_no',
        'chassis_no',
        'license_serial_no',
        'license_owner',
        'owner_tax_or_tc_no',
        'inspection_date',
        'exhaust_date',
        'insurance_end_date',
        'kasko_end_date',
        'is_active',
        'notes',
        'public_image_upload_token',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'inspection_date' => 'date',
        'exhaust_date' => 'date',
        'insurance_end_date' => 'date',
        'kasko_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Vehicle $vehicle) {
            if (!$vehicle->public_image_upload_token) {
                $vehicle->public_image_upload_token = Str::random(40);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function fuels(): HasMany
    {
        return $this->hasMany(Fuel::class)->orderByDesc('date');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class)->orderBy('sort_order')->orderByDesc('id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class)
            ->orderByDesc('service_date')
            ->orderByDesc('id');
    }

    public function maintenanceSetting(): HasOne
    {
        return $this->hasOne(VehicleMaintenanceSetting::class);
    }

    public function trafficPenalties(): HasMany
    {
        return $this->hasMany(TrafficPenalty::class)
            ->orderByDesc('penalty_date')
            ->orderByDesc('id');
    }
}
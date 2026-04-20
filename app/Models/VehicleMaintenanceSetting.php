<?php

namespace App\Models;

use App\Models\Fleet\Vehicle;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenanceSetting extends Model
{
    use BelongsToCompany;

    protected $table = 'vehicle_maintenance_settings';

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'oil_change_interval_km',
        'under_lubrication_interval_km',
    ];

    protected $casts = [
        'oil_change_interval_km' => 'integer',
        'under_lubrication_interval_km' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
<?php

namespace App\Models;

use App\Models\User;
use App\Models\Fleet\Vehicle;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenance extends Model
{
    use BelongsToCompany;

    protected $table = 'vehicle_maintenances';

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'created_by',
        'maintenance_type',
        'title',
        'service_date',
        'service_name',
        'description',
        'amount',
        'km',
        'next_service_date',
        'next_service_km',
        'status',
    ];

    protected $casts = [
        'service_date' => 'date',
        'next_service_date' => 'date',
        'amount' => 'decimal:2',
        'km' => 'integer',
        'next_service_km' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
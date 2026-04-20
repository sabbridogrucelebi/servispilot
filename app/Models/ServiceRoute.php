<?php

namespace App\Models;

use App\Models\Trip;
use App\Models\Company;
use App\Models\Customer;
use App\Models\RouteStop;
use App\Models\Fleet\Driver;
use App\Models\Fleet\Vehicle;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRoute extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'vehicle_id',
        'driver_id',
        'route_name',
        'route_type',
        'start_location',
        'end_location',
        'departure_time',
        'arrival_time',
        'price',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(RouteStop::class)->orderBy('stop_order');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class)->orderByDesc('trip_date');
    }
}
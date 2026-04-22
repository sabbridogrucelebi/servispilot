<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToCompany;
use App\Models\CustomerServiceRoute;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\Driver;

use App\Models\Concerns\LogsActivity;

class Trip extends Model
{
    use HasFactory, BelongsToCompany, LogsActivity;

    protected $fillable = [
        'company_id',
        'service_route_id',
        'vehicle_id',
        'morning_vehicle_id',
        'evening_vehicle_id',
        'driver_id',
        'trip_date',
        'trip_status',
        'trip_price',
        'notes',
    ];

    protected $casts = [
        'trip_date'  => 'date',
        'trip_price' => 'decimal:2',
    ];

    public function serviceRoute()
    {
        return $this->belongsTo(CustomerServiceRoute::class, 'service_route_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function morningVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'morning_vehicle_id');
    }

    public function eveningVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'evening_vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function getFormattedPriceAttribute()
    {
        if ($this->trip_price === null) {
            return null;
        }

        return number_format($this->trip_price, 2, ',', '.');
    }

    public function getIsWeekendAttribute()
    {
        if (!$this->trip_date) {
            return false;
        }

        return $this->trip_date->isWeekend();
    }

    public function getDayNameAttribute()
    {
        if (!$this->trip_date) {
            return null;
        }

        return match ($this->trip_date->dayOfWeek) {
            0 => 'Pazar',
            1 => 'Pazartesi',
            2 => 'Salı',
            3 => 'Çarşamba',
            4 => 'Perşembe',
            5 => 'Cuma',
            6 => 'Cumartesi',
        };
    }

    public function getDisplayVehiclePlateAttribute()
    {
        if ($this->morningVehicle?->plate && $this->eveningVehicle?->plate) {
            return $this->morningVehicle->plate . ' / ' . $this->eveningVehicle->plate;
        }

        if ($this->morningVehicle?->plate) {
            return $this->morningVehicle->plate;
        }

        if ($this->eveningVehicle?->plate) {
            return $this->eveningVehicle->plate;
        }

        return $this->vehicle?->plate;
    }
}
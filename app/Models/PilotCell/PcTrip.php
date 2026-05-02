<?php

namespace App\Models\PilotCell;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\Driver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PcTrip extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'pc_route_id',
        'driver_id',
        'vehicle_id',
        'trip_date',
        'started_at',
        'ended_at',
        'status',
        'direction',
        'last_lat',
        'last_lng',
        'last_speed',
        'last_heading',
        'last_location_at',
    ];

    protected $casts = [
        'trip_date' => 'date',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(PcRoute::class, 'pc_route_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(PcTripAttendance::class, 'pc_trip_id');
    }

    public function locationLogs(): HasMany
    {
        return $this->hasMany(PcLocationLog::class, 'pc_trip_id');
    }
}

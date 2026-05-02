<?php

namespace App\Models\PilotCell;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\Driver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PcLocationLog extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'pc_trip_id',
        'driver_id',
        'vehicle_id',
        'location',
        'accuracy',
        'speed',
        'heading',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'accuracy' => 'decimal:2',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(PcTrip::class, 'pc_trip_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Scope to select lat/lng from POINT location
     */
    public function scopeWithLatLng($query)
    {
        return $query->addSelect([
            'lat' => DB::raw('ST_X(location)'),
            'lng' => DB::raw('ST_Y(location)'),
        ]);
    }
}

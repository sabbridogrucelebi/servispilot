<?php

namespace App\Models\PilotCell;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\Driver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PcRoute extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'name',
        'service_no',
        'vehicle_id',
        'driver_id',
        'driver_name',
        'driver_phone',
        'hostess_name',
        'hostess_phone',
        'direction',
        'start_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(PcStudent::class, 'pc_route_students', 'pc_route_id', 'pc_student_id')
            ->withPivot('stop_order')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class, 'pc_route_user', 'pc_route_id', 'user_id')->withPivot('personnel_type')->withTimestamps();
    }

    public function trips(): HasMany
    {
        return $this->hasMany(PcTrip::class, 'pc_route_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}

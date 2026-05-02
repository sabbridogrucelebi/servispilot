<?php

namespace App\Models\PilotCell;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PcStudent extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'parent_user_id',
        'parent2_user_id',
        'name',
        'grade',
        'student_no',
        'phone',
        'address',
        'parent1_name',
        'parent1_phone',
        'parent2_name',
        'parent2_phone',
        'monthly_fee',
        'pickup_location',
        'dropoff_location',
        'pickup_radius',
        'dropoff_radius',
        'geofence_radius',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'geofence_radius' => 'integer',
    ];

    protected $hidden = [
        'pickup_location',
        'dropoff_location',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function parent2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent2_user_id');
    }

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(PcRoute::class, 'pc_route_students', 'pc_student_id', 'pc_route_id')
            ->withPivot('stop_order')
            ->withTimestamps();
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(PcTripAttendance::class, 'pc_student_id');
    }

    public function debts(): HasMany
    {
        return $this->hasMany(PcStudentDebt::class, 'pc_student_id')->orderBy('year')->orderBy('month_number');
    }
}

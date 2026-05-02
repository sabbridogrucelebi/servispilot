<?php

namespace App\Models\PilotCell;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PcTripAttendance extends Model
{
    protected $table = 'pc_trip_attendance';

    protected $fillable = [
        'pc_trip_id',
        'pc_student_id',
        'boarding_status',
        'boarded_at',
        'alighted_at',
    ];

    protected $casts = [
        'boarded_at' => 'datetime',
        'alighted_at' => 'datetime',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(PcTrip::class, 'pc_trip_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(PcStudent::class, 'pc_student_id');
    }
}

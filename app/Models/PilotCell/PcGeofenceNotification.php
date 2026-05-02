<?php

namespace App\Models\PilotCell;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PcGeofenceNotification extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'pc_trip_id',
        'pc_student_id',
        'notification_type',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
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

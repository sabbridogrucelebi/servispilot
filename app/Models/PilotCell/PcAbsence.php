<?php

namespace App\Models\PilotCell;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PcAbsence extends Model
{
    use BelongsToCompany;

    protected $table = 'pc_absences';

    protected $fillable = [
        'company_id',
        'pc_student_id',
        'absence_date',
        'reported_by',
        'reason',
    ];

    protected $casts = [
        'absence_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(PcStudent::class, 'pc_student_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reported_by');
    }
}

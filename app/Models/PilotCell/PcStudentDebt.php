<?php

namespace App\Models\PilotCell;

use Illuminate\Database\Eloquent\Model;

class PcStudentDebt extends Model
{
    use \App\Models\Concerns\BelongsToCompany;

    protected $fillable = [
        'company_id',
        'pc_student_id',
        'month_name',
        'month_number',
        'year',
        'amount',
        'paid_amount',
        'is_paid',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(PcStudent::class, 'pc_student_id');
    }
}

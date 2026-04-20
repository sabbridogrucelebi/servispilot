<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Fleet\Driver;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'driver_id',
        'period_month',
        'base_salary',
        'extra_payment',
        'deduction',
        'advance_payment',
        'net_salary',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'extra_payment' => 'decimal:2',
        'deduction' => 'decimal:2',
        'advance_payment' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
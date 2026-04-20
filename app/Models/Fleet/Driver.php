<?php

namespace App\Models\Fleet;

use App\Models\Company;
use App\Models\Document;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Driver extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'full_name',
        'tc_no',
        'phone',
        'email',
        'license_class',
        'src_type',
        'birth_date',
        'start_date',
        'base_salary',
        'is_active',
        'address',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'start_date' => 'date',
        'base_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class)->orderByDesc('period_month');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
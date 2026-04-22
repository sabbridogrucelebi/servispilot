<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToCompany;

class CustomerContract extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'customer_id',
        'year',
        'start_date',
        'end_date',
        'file_path',
        'original_name',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'year'       => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getIsActiveAttribute(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return $this->end_date->endOfDay()->isFuture() || $this->end_date->isToday();
    }
}
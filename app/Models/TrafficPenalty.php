<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToCompany;
use Carbon\Carbon;

use App\Models\Concerns\LogsActivity;

class TrafficPenalty extends Model
{
    use HasFactory, BelongsToCompany, LogsActivity;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'penalty_no',
        'penalty_date',
        'penalty_time',
        'penalty_article',
        'penalty_location',
        'penalty_amount',
        'discounted_amount',
        'driver_name',
        'payment_date',
        'paid_amount',
        'payment_status',
        'traffic_penalty_document',
        'payment_receipt',
        'notes',
    ];

    protected $casts = [
        'penalty_date'      => 'date',
        'payment_date'      => 'date',
        'penalty_amount'    => 'decimal:2',
        'discounted_amount' => 'decimal:2',
        'paid_amount'       => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(\App\Models\Fleet\Vehicle::class, 'vehicle_id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function getDiscountDeadlineAttribute()
    {
        return $this->penalty_date
            ? Carbon::parse($this->penalty_date)->addMonth()
            : null;
    }

    public function getIsDiscountEligibleAttribute(): bool
    {
        if (!$this->payment_date || !$this->penalty_date) {
            return false;
        }

        return Carbon::parse($this->payment_date)->lte(
            Carbon::parse($this->penalty_date)->addMonth()
        );
    }

    public function getCalculatedPayableAmountAttribute(): float
    {
        if ($this->payment_date) {
            return $this->is_discount_eligible
                ? (float) $this->discounted_amount
                : (float) $this->penalty_amount;
        }

        return now()->lte(Carbon::parse($this->penalty_date)->addMonth())
            ? (float) $this->discounted_amount
            : (float) $this->penalty_amount;
    }

    public function getRemainingDaysForDiscountAttribute(): ?int
    {
        if (!$this->penalty_date) {
            return null;
        }

        $deadline = Carbon::parse($this->penalty_date)->addMonth();

        if (now()->gt($deadline)) {
            return 0;
        }

        return (int) now()->startOfDay()->diffInDays($deadline->startOfDay(), false);
    }
}
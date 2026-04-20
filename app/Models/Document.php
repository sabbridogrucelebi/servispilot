<?php

namespace App\Models;

use App\Models\Fleet\Driver;
use App\Models\Fleet\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'company_id',
        'documentable_id',
        'documentable_type',
        'document_name',
        'document_type',
        'issuer_name',
        'start_date',
        'end_date',
        'archived_at',
        'file_path',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'archived_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isExpired(): bool
    {
        return !is_null($this->end_date) && $this->end_date->isPast();
    }

    public function isVehicleDocument(): bool
    {
        return $this->documentable_type === Vehicle::class;
    }

    public function isDriverDocument(): bool
    {
        return $this->documentable_type === Driver::class;
    }

    public function remainingDays(): ?int
    {
        if (is_null($this->end_date)) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false);
    }

    public function isExpiringWithin(int $days = 7): bool
    {
        if (is_null($this->end_date)) {
            return false;
        }

        $today = now()->startOfDay();
        $target = now()->copy()->addDays($days)->endOfDay();

        return $this->end_date->between($today, $target);
    }
}
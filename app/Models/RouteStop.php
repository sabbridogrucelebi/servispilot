<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStop extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'service_route_id',
        'stop_name',
        'stop_order',
        'stop_time',
        'location',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function serviceRoute(): BelongsTo
    {
        return $this->belongsTo(ServiceRoute::class);
    }
}
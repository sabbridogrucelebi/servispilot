<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleTrackingSetting extends Model
{
    use \App\Models\Concerns\BelongsToCompany;

    protected $fillable = [
        'company_id',
        'provider',
        'username',
        'password',
        'app_id',
        'app_key',
        'api_key',
        'is_active',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

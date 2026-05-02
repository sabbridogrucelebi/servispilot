<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollLock extends Model
{
    use \App\Models\Concerns\BelongsToCompany;

    protected $fillable = ['company_id', 'period', 'is_locked'];
}

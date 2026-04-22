<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollLock extends Model
{
    protected $fillable = ['period', 'is_locked'];
}

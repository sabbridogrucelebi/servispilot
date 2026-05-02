<?php

namespace App\Models\PilotCell;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PcRouteStudent extends Pivot
{
    protected $table = 'pc_route_students';

    protected $fillable = [
        'pc_route_id',
        'pc_student_id',
        'stop_order',
    ];
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            [
                'label' => 'PilotCell İzleme',
                'key' => 'pilotcell.view',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'label' => 'PilotCell Yönetimi',
                'key' => 'pilotcell.manage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'label' => 'PilotCell Şoför Yetkisi',
                'key' => 'pilotcell.drive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('permissions')->insert($permissions);
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('key', [
            'pilotcell.view',
            'pilotcell.manage',
            'pilotcell.drive'
        ])->delete();
    }
};

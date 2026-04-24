<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            ['key' => 'drivers.create',   'label' => 'Personel Ekleme'],
            ['key' => 'drivers.edit',     'label' => 'Personel Düzenleme'],
            ['key' => 'drivers.delete',   'label' => 'Personel Silme'],
            ['key' => 'logs.view',        'label' => 'Sistem Loglarını Görüntüleme'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['key' => $permission['key']], $permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::whereIn('key', [
            'drivers.create', 
            'drivers.edit', 
            'drivers.delete', 
            'logs.view'
        ])->delete();
    }
};


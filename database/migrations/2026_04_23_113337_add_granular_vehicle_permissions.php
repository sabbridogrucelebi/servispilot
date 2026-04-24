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
            ['key' => 'vehicles.create', 'label' => 'Araç Ekleme'],
            ['key' => 'vehicles.edit',   'label' => 'Araç Düzenleme'],
            ['key' => 'vehicles.delete', 'label' => 'Araç Silme'],
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
        Permission::whereIn('key', ['vehicles.create', 'vehicles.edit', 'vehicles.delete'])->delete();
    }
};

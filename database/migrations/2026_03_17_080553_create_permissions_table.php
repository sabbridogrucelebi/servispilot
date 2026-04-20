<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->timestamps();
        });

        $permissions = [
            ['key' => 'dashboard.view', 'label' => 'Dashboard'],
            ['key' => 'vehicles.view', 'label' => 'Araçlar'],
            ['key' => 'drivers.view', 'label' => 'Şoförler'],
            ['key' => 'customers.view', 'label' => 'Müşteriler'],
            ['key' => 'service_routes.view', 'label' => 'Servis Hatları'],
            ['key' => 'route_stops.view', 'label' => 'Duraklar'],
            ['key' => 'trips.view', 'label' => 'Puantaj'],
            ['key' => 'payrolls.view', 'label' => 'Maaş'],
            ['key' => 'documents.view', 'label' => 'Belgeler'],
            ['key' => 'fuels.view', 'label' => 'Yakıt'],
            ['key' => 'company_users.view', 'label' => 'Kullanıcılar'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'key' => $permission['key'],
                'label' => $permission['label'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
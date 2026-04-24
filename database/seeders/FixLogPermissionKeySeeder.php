<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FixLogPermissionKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Permission::where('key', 'activity_logs.view')->update(['key' => 'logs.view']);
        
        // Ensure logs.view exists if it didn't before
        \App\Models\Permission::updateOrCreate(
            ['key' => 'logs.view'],
            ['label' => 'Sistem Loglarını Görüntüleme']
        );
    }
}

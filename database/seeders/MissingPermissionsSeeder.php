<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MissingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['key' => 'finance.view', 'label' => 'Finans Görüntüleme'],
            ['key' => 'finance.create', 'label' => 'Finans Ekleme'],
            ['key' => 'finance.edit', 'label' => 'Finans Düzenleme'],
            ['key' => 'finance.delete', 'label' => 'Finans Silme'],
            ['key' => 'reports.export', 'label' => 'Rapor Dışa Aktarma'],
            ['key' => 'chat.view', 'label' => 'Mesajlaşma Görüntüleme'],
            ['key' => 'chat.create', 'label' => 'Mesajlaşma Gönderme'],
        ];

        foreach ($permissions as $p) {
            \App\Models\Permission::updateOrCreate(['key' => $p['key']], ['label' => $p['label']]);
        }
    }
}

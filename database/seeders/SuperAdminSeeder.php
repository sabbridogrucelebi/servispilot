<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyModule;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin Kullanıcı ──
        $superAdmin = User::firstOrCreate(
            ['email' => 'sabbridogru@gmail.com'],
            [
                'name'           => 'Sabri Doğru',
                'password'       => 'Celebi!2023.',
                'role'           => 'company_admin',
                'is_active'      => true,
                'is_super_admin' => true,
            ]
        );

        // Super admin'e is_super_admin bayrağını güncelle (eğer kullanıcı zaten varsa)
        if (!$superAdmin->is_super_admin) {
            $superAdmin->update(['is_super_admin' => true]);
        }

        // Super admin'e tüm yetkileri ver
        $permissionIds = Permission::pluck('id')->toArray();
        $superAdmin->permissions()->syncWithoutDetaching($permissionIds);

        $this->command->info('✅ Super Admin oluşturuldu: sabbridogru@gmail.com');

        // ── Mevcut firmaların modüllerini kontrol et ──
        $companiesWithoutModules = Company::whereDoesntHave('modules')->get();

        foreach ($companiesWithoutModules as $company) {
            $company->activateAllModules();
            $this->command->info("✅ {$company->name} firmasına tüm modüller atandı.");
        }

        $this->command->info('');
        $this->command->info('🚀 ServisPilot SaaS kurulumu tamamlandı!');
        $this->command->info('   Super Admin: sabbridogru@gmail.com');
        $this->command->info('   Super Admin Paneli: /super-admin/dashboard');
    }
}

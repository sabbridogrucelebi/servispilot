<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Yeni eklenen granüler yetkileri (vehicles.create/edit/delete,
     * drivers.create/edit/delete, logs.view) mevcut TÜM firma
     * admin kullanıcılarına otomatik olarak atar.
     *
     * Böylece bu güncellemeden önce oluşturulmuş firma admini
     * kullanıcılar yeni butonları görmeye ve kullanmaya devam eder.
     */
    public function up(): void
    {
        $keys = [
            'vehicles.create',
            'vehicles.edit',
            'vehicles.delete',
            'drivers.create',
            'drivers.edit',
            'drivers.delete',
            'logs.view',
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('key', $keys)
            ->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        $adminUserIds = DB::table('users')
            ->where('role', 'company_admin')
            ->pluck('id');

        foreach ($adminUserIds as $userId) {
            foreach ($permissionIds as $permissionId) {
                $exists = DB::table('user_permissions')
                    ->where('user_id', $userId)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (!$exists) {
                    DB::table('user_permissions')->insert([
                        'user_id'       => $userId,
                        'permission_id' => $permissionId,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No-op: Kullanıcılardan yetki geri almak istemiyoruz.
        // Gerekirse manuel olarak yönetici panelinden kaldırılır.
    }
};

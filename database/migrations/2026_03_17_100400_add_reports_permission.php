<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permission = DB::table('permissions')->where('key', 'reports.view')->first();

        if (!$permission) {
            $permissionId = DB::table('permissions')->insertGetId([
                'key' => 'reports.view',
                'label' => 'Raporlar',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $permissionId = $permission->id;
        }

        $adminUsers = DB::table('users')->where('role', 'company_admin')->get();

        foreach ($adminUsers as $user) {
            $exists = DB::table('user_permissions')
                ->where('user_id', $user->id)
                ->where('permission_id', $permissionId)
                ->exists();

            if (!$exists) {
                DB::table('user_permissions')->insert([
                    'user_id' => $user->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $permission = DB::table('permissions')->where('key', 'reports.view')->first();

        if ($permission) {
            DB::table('user_permissions')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
        }
    }
};
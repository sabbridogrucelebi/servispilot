<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class UpdatePermissionsSeeder extends Seeder
{
    public function run()
    {

$permissions = [
    // Customers
    ['key' => 'customers.view', 'label' => 'Müşterileri Görüntüleme'],
    ['key' => 'customers.create', 'label' => 'Müşteri Ekleme'],
    ['key' => 'customers.edit', 'label' => 'Müşteri Düzenleme'],
    ['key' => 'customers.delete', 'label' => 'Müşteri Silme'],
    
    // Vehicles
    ['key' => 'vehicles.view', 'label' => 'Araçları Görüntüleme'],
    ['key' => 'vehicles.create', 'label' => 'Araç Ekleme'],
    ['key' => 'vehicles.edit', 'label' => 'Araç Düzenleme'],
    ['key' => 'vehicles.delete', 'label' => 'Araç Silme'],
    
    // Drivers
    ['key' => 'drivers.view', 'label' => 'Personelleri Görüntüleme'],
    ['key' => 'drivers.create', 'label' => 'Personel Ekleme'],
    ['key' => 'drivers.edit', 'label' => 'Personel Düzenleme'],
    ['key' => 'drivers.delete', 'label' => 'Personel Silme'],
    
    // Trips
    ['key' => 'trips.view', 'label' => 'Seferleri Görüntüleme'],
    ['key' => 'trips.create', 'label' => 'Sefer Ekleme'],
    ['key' => 'trips.edit', 'label' => 'Sefer Düzenleme'],
    ['key' => 'trips.delete', 'label' => 'Sefer Silme'],
    
    // Fuels
    ['key' => 'fuels.view', 'label' => 'Yakıtları Görüntüleme'],
    ['key' => 'fuels.create', 'label' => 'Yakıt Ekleme'],
    ['key' => 'fuels.edit', 'label' => 'Yakıt Düzenleme'],
    ['key' => 'fuels.delete', 'label' => 'Yakıt Silme'],
    
    // Fuel Stations
    ['key' => 'fuel_stations.view', 'label' => 'İstasyonları Görüntüleme'],
    ['key' => 'fuel_stations.create', 'label' => 'İstasyon Ekleme'],
    ['key' => 'fuel_stations.edit', 'label' => 'İstasyon Düzenleme'],
    ['key' => 'fuel_stations.delete', 'label' => 'İstasyon Silme'],
    
    // Maintenances
    ['key' => 'maintenances.view', 'label' => 'Bakımları Görüntüleme'],
    ['key' => 'maintenances.create', 'label' => 'Bakım Ekleme'],
    ['key' => 'maintenances.edit', 'label' => 'Bakım Düzenleme'],
    ['key' => 'maintenances.delete', 'label' => 'Bakım Silme'],
    
    // Penalties
    ['key' => 'penalties.view', 'label' => 'Cezaları Görüntüleme'],
    ['key' => 'penalties.create', 'label' => 'Ceza Ekleme'],
    ['key' => 'penalties.edit', 'label' => 'Ceza Düzenleme'],
    ['key' => 'penalties.delete', 'label' => 'Ceza Silme'],
    
    // Documents
    ['key' => 'documents.view', 'label' => 'Belgeleri Görüntüleme'],
    ['key' => 'documents.create', 'label' => 'Belge Ekleme'],
    ['key' => 'documents.edit', 'label' => 'Belge Düzenleme'],
    ['key' => 'documents.delete', 'label' => 'Belge Silme'],
    
    // Payrolls
    ['key' => 'payrolls.view', 'label' => 'Maaşları Görüntüleme'],
    ['key' => 'payrolls.create', 'label' => 'Maaş Ekleme'],
    ['key' => 'payrolls.edit', 'label' => 'Maaş Düzenleme'],
    ['key' => 'payrolls.delete', 'label' => 'Maaş Silme'],
    
    // Reports
    ['key' => 'reports.view', 'label' => 'Raporları Görüntüleme'],
    
    // Logs
    ['key' => 'logs.view', 'label' => 'Sistem Loglarını Görüntüleme'],
    
    // Company Users
    ['key' => 'company_users.view', 'label' => 'Kullanıcıları Görüntüleme'],
    ['key' => 'company_users.create', 'label' => 'Kullanıcı Ekleme'],
    ['key' => 'company_users.edit', 'label' => 'Kullanıcı Düzenleme'],
    ['key' => 'company_users.delete', 'label' => 'Kullanıcı Silme'],
    
    // Dashboard
    ['key' => 'dashboard.view', 'label' => 'Gösterge Panelini Görüntüleme'],
];

foreach ($permissions as $p) {
    Permission::updateOrCreate(['key' => $p['key']], $p);
}

echo "Permissions updated successfully.\n";

    }
}

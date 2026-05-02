<?php
$content = file_get_contents('app/Http/Controllers/Api/V1/MaintenanceApiController.php');

// 1. Update options method
$optionsSearch = "    public function options(Request \$request)
    {
        \$companyId = \$this->getCompanyId();
        
        \$vehicles = \App\Models\Fleet\Vehicle::where('company_id', \$companyId)
            ->where('is_active', true)
            ->get(['id', 'plate']);

        return \$this->successResponse([
            'vehicles' => \$vehicles,
        ], 'Form seçenekleri başarıyla getirildi.');
    }";

$optionsReplace = "    public function options(Request \$request)
    {
        \$companyId = \$this->getCompanyId();
        
        \$vehicles = \App\Models\Fleet\Vehicle::where('company_id', \$companyId)
            ->where('is_active', true)
            ->get(['id', 'plate']);
            
        \$mechanics = \App\Models\Mechanic::where('company_id', \$companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name');

        return \$this->successResponse([
            'vehicles' => \$vehicles,
            'mechanics' => \$mechanics,
        ], 'Form seçenekleri başarıyla getirildi.');
    }";

$content = str_replace(str_replace("\n", "\r\n", $optionsSearch), str_replace("\n", "\r\n", $optionsReplace), $content);

// 2. Add API Settings & Mechanic Methods before the last brace
$newMethods = "
    public function settings(Request \$request)
    {
        if (!\$this->userHasPermission(\$request->user(), 'maintenances.view')) {
            return \$this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        \$companyId = \$this->getCompanyId();

        \$vehicles = \App\Models\Fleet\Vehicle::with('maintenanceSetting')
            ->where('company_id', \$companyId)
            ->orderBy('plate')
            ->get(['id', 'plate', 'brand', 'model']);
            
        \$mechanics = \App\Models\Mechanic::where('company_id', \$companyId)
            ->orderBy('name')
            ->get();

        return \$this->successResponse([
            'vehicles' => \$vehicles,
            'mechanics' => \$mechanics,
        ], 'Ayarlar başarıyla getirildi.');
    }

    public function saveSettings(Request \$request)
    {
        if (!\$this->userHasPermission(\$request->user(), 'maintenances.view')) {
            return \$this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        \$companyId = \$this->getCompanyId();
        \$settings = \$request->input('settings', []);

        foreach (\$settings as \$settingData) {
            if (!isset(\$settingData['vehicle_id'])) continue;
            
            // Sadece bu şirkete ait araçların ayarlarını güncelle
            \$vehicle = \App\Models\Fleet\Vehicle::where('company_id', \$companyId)->find(\$settingData['vehicle_id']);
            if (!\$vehicle) continue;

            \App\Models\VehicleMaintenanceSetting::updateOrCreate(
                ['vehicle_id' => \$settingData['vehicle_id']],
                [
                    'oil_change_interval_km' => \$settingData['oil_change_interval_km'] ?? null,
                    'under_lubrication_interval_km' => \$settingData['under_lubrication_interval_km'] ?? null,
                ]
            );
        }

        return \$this->successResponse(null, 'Ayarlar başarıyla kaydedildi.');
    }

    public function storeMechanic(Request \$request)
    {
        if (!\$this->userHasPermission(\$request->user(), 'vehicles.view')) {
            return \$this->errorResponse('Yetkiniz yok.', 403);
        }

        \$validated = \$request->validate([
            'name' => 'required|string|max:255',
        ]);

        \$mechanic = \App\Models\Mechanic::create([
            'company_id' => \$this->getCompanyId(),
            'name' => \$validated['name'],
            'is_active' => true,
        ]);

        return \$this->successResponse(\$mechanic, 'Usta başarıyla eklendi.');
    }

    public function updateMechanic(Request \$request, \$id)
    {
        if (!\$this->userHasPermission(\$request->user(), 'vehicles.view')) {
            return \$this->errorResponse('Yetkiniz yok.', 403);
        }

        \$mechanic = \App\Models\Mechanic::where('company_id', \$this->getCompanyId())->find(\$id);
        if (!\$mechanic) return \$this->errorResponse('Usta bulunamadı.', 404);

        \$validated = \$request->validate([
            'name' => 'required|string|max:255',
        ]);

        \$oldName = \$mechanic->name;
        \$mechanic->update(['name' => \$validated['name']]);

        if (\$oldName !== \$validated['name']) {
            \App\Models\VehicleMaintenance::where('company_id', \$this->getCompanyId())
                ->where('service_name', \$oldName)
                ->update(['service_name' => \$validated['name']]);
        }

        return \$this->successResponse(\$mechanic, 'Usta başarıyla güncellendi.');
    }

    public function toggleMechanic(Request \$request, \$id)
    {
        if (!\$this->userHasPermission(\$request->user(), 'vehicles.view')) {
            return \$this->errorResponse('Yetkiniz yok.', 403);
        }

        \$mechanic = \App\Models\Mechanic::where('company_id', \$this->getCompanyId())->find(\$id);
        if (!\$mechanic) return \$this->errorResponse('Usta bulunamadı.', 404);

        \$mechanic->update(['is_active' => !\$mechanic->is_active]);

        return \$this->successResponse(\$mechanic, 'Usta durumu güncellendi.');
    }

    public function destroyMechanic(Request \$request, \$id)
    {
        if (!\$this->userHasPermission(\$request->user(), 'vehicles.view')) {
            return \$this->errorResponse('Yetkiniz yok.', 403);
        }

        \$mechanic = \App\Models\Mechanic::where('company_id', \$this->getCompanyId())->find(\$id);
        if (!\$mechanic) return \$this->errorResponse('Usta bulunamadı.', 404);

        \$mechanic->delete();

        return \$this->successResponse(null, 'Usta başarıyla silindi.');
    }
}
";

$content = preg_replace('/}\s*$/', $newMethods, $content);
file_put_contents('app/Http/Controllers/Api/V1/MaintenanceApiController.php', $content);
echo "DONE\n";

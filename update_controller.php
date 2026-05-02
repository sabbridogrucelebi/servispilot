<?php
$content = file_get_contents('app/Http/Controllers/MaintenanceController.php');

$search = "        \$masters = VehicleMaintenance::query()
            ->whereNotNull('service_name')
            ->where('service_name', '!=', '')
            ->select('service_name')
            ->distinct()
            ->orderBy('service_name')
            ->pluck('service_name');";

$replace = "        \$masters = \App\Models\Mechanic::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name');";

$content = str_replace(str_replace("\n", "\r\n", $search), str_replace("\n", "\r\n", $replace), $content);

$crudMethods = "
    public function storeMechanic(Request \$request)
    {
        abort_unless(auth()->user()->hasPermission('vehicles.view'), 403);

        \$validated = \$request->validate([
            'name' => 'required|string|max:255',
        ]);

        \App\Models\Mechanic::create([
            'company_id' => auth()->user()->company_id,
            'name' => \$validated['name'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Usta başarıyla eklendi.');
    }

    public function updateMechanic(Request \$request, \App\Models\Mechanic \$mechanic)
    {
        abort_unless(auth()->user()->hasPermission('vehicles.view'), 403);
        
        if (\$mechanic->company_id !== auth()->user()->company_id) abort(403);

        \$oldName = \$mechanic->name;
        
        \$validated = \$request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        \$mechanic->update(['name' => \$validated['name']]);
        
        if (\$oldName !== \$validated['name']) {
            \App\Models\VehicleMaintenance::where('company_id', auth()->user()->company_id)
                ->where('service_name', \$oldName)
                ->update(['service_name' => \$validated['name']]);
        }
        
        return back()->with('success', 'Usta güncellendi ve geçmiş kayıtlara yansıtıldı.');
    }

    public function toggleMechanic(\App\Models\Mechanic \$mechanic)
    {
        abort_unless(auth()->user()->hasPermission('vehicles.view'), 403);
        if (\$mechanic->company_id !== auth()->user()->company_id) abort(403);

        \$mechanic->update(['is_active' => !\$mechanic->is_active]);

        return back()->with('success', 'Usta durumu güncellendi.');
    }

    public function destroyMechanic(\App\Models\Mechanic \$mechanic)
    {
        abort_unless(auth()->user()->hasPermission('vehicles.view'), 403);
        if (\$mechanic->company_id !== auth()->user()->company_id) abort(403);

        \$mechanic->delete();

        return back()->with('success', 'Usta başarıyla silindi.');
    }
}
";

// Replace the final closing brace with our new methods + closing brace
$content = preg_replace('/}\s*$/', $crudMethods, $content);

file_put_contents('app/Http/Controllers/MaintenanceController.php', $content);
echo "DONE\n";

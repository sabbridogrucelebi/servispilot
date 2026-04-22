<?php

namespace App\Http\Controllers;

use ZipArchive;
use App\Models\Fuel;
use App\Models\Trip;
use App\Models\Document;
use App\Models\Payroll;
use App\Models\FuelStation;
use App\Models\TrafficPenalty;
use Illuminate\Http\Request;
use App\Exports\VehiclesExport;
use App\Models\Fleet\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\Fleet\VehicleImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ArventoService;
use App\Models\VehicleTrackingSetting;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with(['fuels' => function($q) {
            $q->latest('date')->latest('id');
        }])->latest()->get();

        return view('vehicles.index', compact('vehicles'));
    }

    public function exportExcel()
    {
        $fileName = 'Araclar_' . now()->format('d-m-Y_H-i') . '.xlsx';

        // Aktivite Kaydı
        \App\Models\ActivityLog::create([
            'company_id'   => auth()->user()->company_id,
            'user_id'      => auth()->id(),
            'module'       => 'vehicles',
            'action'       => 'exported',
            'subject_type' => Vehicle::class,
            'title'        => 'Araç Listesi Dışa Aktarıldı',
            'description'  => 'Tüm araç listesi Excel formatında indirildi.',
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return Excel::download(new VehiclesExport(), $fileName);
    }

    public function show(Vehicle $vehicle)
    {
        if (!$vehicle->public_image_upload_token) {
            $vehicle->update([
                'public_image_upload_token' => Str::random(40),
            ]);
            $vehicle->refresh();
        }

        $income = Trip::where('vehicle_id', $vehicle->id)->sum('trip_price');

        $fuel = Fuel::where('vehicle_id', $vehicle->id)->sum('total_cost');

        $salary = Payroll::whereHas('driver', function ($q) use ($vehicle) {
            $q->where('vehicle_id', $vehicle->id);
        })->sum('net_salary');

        $profit = $income - ($fuel + $salary);

        $recentFuels = Fuel::with(['station', 'vehicle'])
            ->where('vehicle_id', $vehicle->id)
            ->latest('date')
            ->latest('id')
            ->get();

        $stationSummaries = FuelStation::with(['fuels', 'payments'])
            ->orderBy('name')
            ->get()
            ->map(function ($station) {
                $totalLiters = (float) $station->fuels->sum('liters');
                $totalAmount = (float) $station->fuels->sum('total_cost');
                $totalPaid = (float) $station->payments->sum('amount');

                return (object) [
                    'id' => $station->id,
                    'name' => $station->name,
                    'total_liters' => $totalLiters,
                    'total_amount' => $totalAmount,
                    'total_paid' => $totalPaid,
                    'current_debt' => $totalAmount - $totalPaid,
                ];
            });

        $allVehicleDocuments = $vehicle->documents()
            ->latest()
            ->get();

        $activeVehicleDocuments = $allVehicleDocuments->filter(function ($doc) {
            return is_null($doc->archived_at)
                && (is_null($doc->end_date) || $doc->end_date->startOfDay()->gte(now()->startOfDay()));
        })->values();

        $archivedVehicleDocuments = $allVehicleDocuments->filter(function ($doc) {
            return !is_null($doc->archived_at)
                || (!is_null($doc->end_date) && $doc->end_date->startOfDay()->lt(now()->startOfDay()));
        })->values();

        $assignedDrivers = $vehicle->drivers()
            ->latest()
            ->take(5)
            ->get();

        $primaryDriver = $vehicle->drivers()->latest()->first();

        $driverAge = null;
        if ($primaryDriver && !empty($primaryDriver->birth_date)) {
            try {
                $driverAge = \Carbon\Carbon::parse($primaryDriver->birth_date)->age;
            } catch (\Throwable $e) {
                $driverAge = null;
            }
        }

        $tripCount = Trip::where('vehicle_id', $vehicle->id)->count();

        $recentTrips = Trip::where('vehicle_id', $vehicle->id)
            ->latest('trip_date')
            ->take(10)
            ->get();

        $vehicleMaintenances = VehicleMaintenance::with(['vehicle', 'creator'])
            ->where('vehicle_id', $vehicle->id)
            ->latest('service_date')
            ->latest('id')
            ->get();

        $vehiclePenalties = TrafficPenalty::query()
            ->where('vehicle_id', $vehicle->id)
            ->latest('penalty_date')
            ->latest('id')
            ->get();

        $vehicleImages = $vehicle->images()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $latestFuelRecord = $recentFuels
            ->sortByDesc(function ($item) {
                return sprintf(
                    '%s-%010d',
                    optional($item->date)->format('Ymd') ?? '00000000',
                    (int) $item->id
                );
            })
            ->first();

        $currentKm = data_get($latestFuelRecord, 'current_km')
            ?? data_get($latestFuelRecord, 'km')
            ?? data_get($latestFuelRecord, 'vehicle_km')
            ?? data_get($latestFuelRecord, 'odometer')
            ?? data_get($latestFuelRecord, 'kilometer')
            ?? data_get($vehicle, 'current_km')
            ?? data_get($vehicle, 'km')
            ?? data_get($vehicle, 'kilometer')
            ?? 0;

        $publicImageUploadUrl = route('vehicles.public-images.form', [
            'vehicle' => $vehicle->id,
            'token' => $vehicle->public_image_upload_token,
        ]);

        // Arvento Canlı Verileri
        $arventoStats = null;
        $arventoSetting = VehicleTrackingSetting::where('provider', 'arvento')->first();
        if ($arventoSetting) {
            try {
                $arventoService = new ArventoService($arventoSetting);
                $arventoStats = $arventoService->getVehicleDailyStats($vehicle->plate);
            } catch (\Exception $e) {
                \Log::error("Arvento Stats Fetch Error: " . $e->getMessage());
            }
        }

        return view('vehicles.show', compact(
            'vehicle',
            'income',
            'fuel',
            'salary',
            'profit',
            'recentFuels',
            'stationSummaries',
            'activeVehicleDocuments',
            'archivedVehicleDocuments',
            'assignedDrivers',
            'primaryDriver',
            'driverAge',
            'currentKm',
            'tripCount',
            'recentTrips',
            'vehicleMaintenances',
            'vehiclePenalties',
            'vehicleImages',
            'publicImageUploadUrl',
            'arventoStats'
        ));
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plate' => 'required|string|max:20|unique:vehicles,plate',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|string|max:100',
            'vehicle_package' => 'nullable|string|max:100',
            'model_year' => 'nullable|integer|min:1900|max:2100',
            'registration_date' => 'nullable|date',
            'seat_count' => 'nullable|integer|min:1',
            'gear_type' => 'nullable|string|max:50',
            'fuel_type' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'other_color' => 'nullable|string|max:100',
            'engine_no' => 'nullable|string|max:100',
            'chassis_no' => 'nullable|string|max:100',
            'license_serial_no' => 'nullable|string|max:100',
            'license_owner' => 'nullable|string|max:255',
            'owner_tax_or_tc_no' => 'nullable|string|max:50',
            'inspection_date' => 'nullable|date',
            'exhaust_date' => 'nullable|date',
            'insurance_end_date' => 'nullable|date',
            'kasko_end_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'current_km' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:50',
        ]);

        if (($data['color'] ?? null) !== 'Diğer') {
            $data['other_color'] = null;
        }

        $data['company_id'] = auth()->user()->company_id;
        $data['is_active'] = $request->boolean('is_active');
        $data['public_image_upload_token'] = Str::random(40);

        Vehicle::create($data);

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Araç başarıyla eklendi.');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'plate' => 'required|string|max:20|unique:vehicles,plate,' . $vehicle->id,
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|string|max:100',
            'vehicle_package' => 'nullable|string|max:100',
            'model_year' => 'nullable|integer|min:1900|max:2100',
            'registration_date' => 'nullable|date',
            'seat_count' => 'nullable|integer|min:1',
            'gear_type' => 'nullable|string|max:50',
            'fuel_type' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'other_color' => 'nullable|string|max:100',
            'engine_no' => 'nullable|string|max:100',
            'chassis_no' => 'nullable|string|max:100',
            'license_serial_no' => 'nullable|string|max:100',
            'license_owner' => 'nullable|string|max:255',
            'owner_tax_or_tc_no' => 'nullable|string|max:50',
            'inspection_date' => 'nullable|date',
            'exhaust_date' => 'nullable|date',
            'insurance_end_date' => 'nullable|date',
            'kasko_end_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'current_km' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:50',
        ]);

        if (($data['color'] ?? null) !== 'Diğer') {
            $data['other_color'] = null;
        }

        $data['is_active'] = $request->boolean('is_active');

        if (!$vehicle->public_image_upload_token) {
            $data['public_image_upload_token'] = Str::random(40);
        }

        $vehicle->update($data);

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Araç başarıyla güncellendi.');
    }

    public function uploadImage(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_featured' => 'nullable|boolean',
            'image_type' => 'nullable|string|max:50',
        ]);

        $path = $request->file('image')->store('vehicle-images', 'public');

        $isFeatured = $request->boolean('is_featured');

        if ($isFeatured || $vehicle->images()->count() === 0) {
            $vehicle->images()->update(['is_featured' => false]);
            $isFeatured = true;
        }

        $imageType = $data['image_type'] ?? 'other';

        $vehicle->images()->create([
            'title' => $data['title'] ?? $this->resolveImageTypeLabel($imageType),
            'file_path' => $path,
            'is_featured' => $isFeatured,
            'sort_order' => 0,
            'image_type' => $imageType,
            'upload_source' => 'panel',
        ]);

        return redirect()
            ->route('vehicles.show', ['vehicle' => $vehicle->id, 'tab' => 'images'])
            ->with('success', 'Araç resmi başarıyla yüklendi.');
    }

    public function publicImageUploadForm(Vehicle $vehicle, string $token)
    {
        abort_unless($vehicle->public_image_upload_token && hash_equals($vehicle->public_image_upload_token, $token), 404);

        $imageTypeOptions = $this->imageTypeOptions();

        return view('vehicles.public-upload', compact('vehicle', 'token', 'imageTypeOptions'));
    }

    public function publicImageUploadStore(Request $request, Vehicle $vehicle, string $token)
    {
        abort_unless($vehicle->public_image_upload_token && hash_equals($vehicle->public_image_upload_token, $token), 404);

        $data = $request->validate([
            'image_type' => 'required|string|in:front,right_side,left_side,rear,interior_1,interior_2,dashboard,other',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);

        $path = $request->file('image')->store('vehicle-images', 'public');

        $imageType = $data['image_type'];
        $typeLabel = $this->resolveImageTypeLabel($imageType);

        $existingCount = $vehicle->images()
            ->where('image_type', $imageType)
            ->count();

        $title = $typeLabel;
        if ($existingCount > 0 && $imageType === 'other') {
            $title .= ' #' . ($existingCount + 1);
        }

        $isFeatured = $vehicle->images()->count() === 0;

        if ($isFeatured) {
            $vehicle->images()->update(['is_featured' => false]);
        }

        $vehicle->images()->create([
            'title' => $title,
            'file_path' => $path,
            'is_featured' => $isFeatured,
            'sort_order' => 0,
            'image_type' => $imageType,
            'upload_source' => 'driver_link',
        ]);

        return redirect()
            ->route('vehicles.public-images.form', ['vehicle' => $vehicle->id, 'token' => $token])
            ->with('success', $typeLabel . ' başarıyla yüklendi.');
    }

    public function setFeaturedImage(Vehicle $vehicle, VehicleImage $image)
    {
        if ($image->vehicle_id !== $vehicle->id) {
            abort(404);
        }

        $vehicle->images()->update(['is_featured' => false]);
        $image->update(['is_featured' => true]);

        return redirect()
            ->route('vehicles.show', ['vehicle' => $vehicle->id, 'tab' => 'images'])
            ->with('success', 'Vitrin resmi güncellendi.');
    }

    public function deleteImage(Vehicle $vehicle, VehicleImage $image)
    {
        if ($image->vehicle_id !== $vehicle->id) {
            abort(404);
        }

        $wasFeatured = $image->is_featured;

        if ($image->file_path && Storage::disk('public')->exists($image->file_path)) {
            Storage::disk('public')->delete($image->file_path);
        }

        $image->delete();

        if ($wasFeatured) {
            $nextImage = $vehicle->images()->first();
            if ($nextImage) {
                $nextImage->update(['is_featured' => true]);
            }
        }

        return redirect()
            ->route('vehicles.show', ['vehicle' => $vehicle->id, 'tab' => 'images'])
            ->with('success', 'Araç resmi silindi.');
    }

    public function uploadDocument(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'document_name' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'issuer_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx|max:20480',
            'notes' => 'nullable|string',
        ]);

        $filePath = null;

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('vehicle-documents', 'public');
        }

        $vehicle->documents()
            ->whereNull('archived_at')
            ->where('document_type', $data['document_type'])
            ->update(['archived_at' => now()]);

        $vehicle->documents()->create([
            'company_id' => auth()->user()->company_id,
            'document_name' => $data['document_name'],
            'document_type' => $data['document_type'],
            'issuer_name' => $data['issuer_name'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'archived_at' => null,
            'file_path' => $filePath,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('vehicles.show', ['vehicle' => $vehicle->id, 'tab' => 'documents'])
            ->with('success', 'Belge başarıyla kaydedildi.');
    }

    public function deleteDocument(Vehicle $vehicle, Document $document)
    {
        if (
            $document->documentable_type !== Vehicle::class ||
            (int) $document->documentable_id !== (int) $vehicle->id
        ) {
            abort(404);
        }

        if (!empty($document->file_path) && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()
            ->route('vehicles.show', ['vehicle' => $vehicle->id, 'tab' => 'documents'])
            ->with('success', 'Araç belgesi silindi.');
    }

    public function downloadDocumentsZip(Vehicle $vehicle)
    {
        $documents = $vehicle->documents()->get();

        $zipFileName = $vehicle->plate . '_Belgeleri_' . now()->format('d-m-Y_H-i') . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'ZIP dosyası oluşturulamadı.');
        }

        foreach ($documents as $document) {
            if (!empty($document->file_path) && Storage::disk('public')->exists($document->file_path)) {
                $absolutePath = Storage::disk('public')->path($document->file_path);
                $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);

                $folder = (!is_null($document->archived_at) || (!is_null($document->end_date) && $document->end_date->isPast()))
                    ? 'Arsiv_Belgeler'
                    : 'Aktif_Belgeler';

                $safeName = preg_replace('/[\\\\\\/:"*?<>|]+/', '-', ($document->document_name ?: 'Belge'));
                $safeType = preg_replace('/[\\\\\\/:"*?<>|]+/', '-', ($document->document_type ?: 'Dosya'));

                $fileNameInZip = $folder . '/' . $safeName . '_' . $safeType . '.' . $extension;

                $zip->addFile($absolutePath, $fileNameInZip);
            }
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function destroy(Vehicle $vehicle)
    {
        foreach ($vehicle->images as $image) {
            if ($image->file_path && Storage::disk('public')->exists($image->file_path)) {
                Storage::disk('public')->delete($image->file_path);
            }
        }

        foreach ($vehicle->documents as $document) {
            if (!empty($document->file_path) && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
        }

        $vehicle->delete();

        return back()->with('success', 'Araç silindi.');
    }

    protected function imageTypeOptions(): array
    {
        return [
            'front' => 'Araç Ön Resmi',
            'right_side' => 'Sağ Yan',
            'left_side' => 'Sol Yan',
            'rear' => 'Arka',
            'interior_1' => 'İç Resim 1',
            'interior_2' => 'İç Resim 2',
            'dashboard' => 'Göğüs',
            'other' => 'Diğer Resimler',
        ];
    }

    protected function resolveImageTypeLabel(?string $type): string
    {
        return $this->imageTypeOptions()[$type] ?? 'Araç Görseli';
    }

    public function aiAssistant(Request $request)
    {
        $question = mb_strtolower($request->get('question', ''), 'UTF-8');
        $companyId = auth()->user()->company_id;
        
        $vehicles = Vehicle::where('company_id', $companyId)->get();
        $totalCount = $vehicles->count();
        
        $answer = "Sistemi taradım. İşte bulduğum sonuçlar:\n\n";

        if (str_contains($question, 'bakım') || str_contains($question, 'servis')) {
            $nearMaintenance = [];
            foreach ($vehicles as $v) {
                $status = $v->maintenance_status;
                if ($status['has_setting'] && ($status['oil_remaining'] < 2000 || $status['lube_remaining'] < 1000)) {
                    $nearMaintenance[] = $v->plate . " (" . number_format($status['oil_remaining'], 0, ',', '.') . " KM kaldı)";
                }
            }
            
            if (count($nearMaintenance) > 0) {
                $answer .= "Şu an bakımı yaklaşan " . count($nearMaintenance) . " aracınız var: " . implode(', ', $nearMaintenance) . ". En kısa sürede randevu almanızı öneririm.";
            } else {
                $answer .= "Tüm araçlarınızın bakım durumu şu an iyi görünüyor. Kritik bir seviyeye ulaşan aracınız yok.";
            }
        } elseif (str_contains($question, 'kaç') || str_contains($question, 'sayı') || str_contains($question, 'toplam')) {
            $types = $vehicles->groupBy('vehicle_type')->map->count();
            $answer .= "Toplam {$totalCount} aracınız bulunuyor.\n";
            foreach ($types as $type => $count) {
                $answer .= "- {$count} adet {$type}\n";
            }
        } elseif (str_contains($question, 'aktif') || str_contains($question, 'pasif')) {
            $active = $vehicles->where('is_active', true)->count();
            $passive = $vehicles->where('is_active', false)->count();
            $answer .= "Filonuzda şu an {$active} aktif, {$passive} pasif araç bulunuyor.";
        } elseif (str_contains($question, 'en çok') && (str_contains($question, 'sefer') || str_contains($question, 'yol'))) {
            $topVehicle = $vehicles->sortByDesc('current_km')->first();
            if ($topVehicle) {
                $answer .= "En çok yol kat eden aracınız {$topVehicle->plate} plakalı araçtır (Toplam: " . number_format($topVehicle->current_km, 0, ',', '.') . " KM).";
            }
        } elseif (preg_match('/[0-9]{2}\s?[a-z]{1,3}\s?[0-9]{2,4}/i', $question, $matches)) {
            $plate = strtoupper(str_replace(' ', '', $matches[0]));
            $v = Vehicle::where('company_id', $companyId)->where('plate', 'like', "%$plate%")->first();
            if ($v) {
                $status = $v->is_active ? 'Aktif' : 'Pasif';
                $answer .= "{$v->plate} plakalı aracı buldum. {$v->brand} {$v->model} model, {$v->vehicle_type} tipinde bir araç. Şu an durumu: {$status}. Güncel kilometresi: " . number_format($v->current_km, 0, ',', '.') . " KM.";
            } else {
                $answer .= "Maalesef {$plate} plakalı bir araç sistemde kayıtlı görünmüyor.";
            }
        } else {
            $answer = "Filonuzla ilgili verileri analiz ettim ancak bu soruya özel bir veri bulamadım. Bakım durumları, araç sayıları veya aktiflik durumları hakkında sorular sorabilirsiniz.";
        }

        return response()->json(['answer' => $answer]);
    }
}
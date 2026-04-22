<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Fleet\Driver;
use App\Models\Fleet\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    protected array $driverDocumentTypes = [
        'Kimlik ve Ehliyet',
        'Adli Sicil Kaydı',
        'SRC 1 Belgesi',
        'SRC 2 Belgesi',
        'Psikoteknik Belgesi',
        'MYK Belgesi',
        'İkametgah Belgesi',
        'Sağlık Raporu Belgesi',
        'İş Sözleşmesi',
        'İşten Çıkış Belgesi',
    ];

    public function index(Request $request)
    {
        $query = Driver::with(['vehicle', 'documents'])->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('tc_no', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('license_class', 'like', '%' . $search . '%')
                    ->orWhere('src_type', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'passive') {
                $query->where('is_active', false);
            }
        }

        $drivers = $query->get()->map(function ($driver) {
            $driver->resolved_document_status = $this->resolveDriverDocumentStatus($driver);
            return $driver;
        });

        if ($request->filled('document_status')) {
            $drivers = $drivers->filter(function ($driver) use ($request) {
                $priority = $driver->resolved_document_status['priority'] ?? 'ok';

                return match ($request->document_status) {
                    'expired' => $priority === 'expired',
                    'expiring' => in_array($priority, ['soon7', 'expiring'], true),
                    'ok' => $priority === 'ok',
                    default => true,
                };
            })->values();
        }

        $vehicles = Vehicle::orderBy('plate')->get();
        $allDrivers = Driver::with('documents')->get()->map(function ($driver) {
            $driver->resolved_document_status = $this->resolveDriverDocumentStatus($driver);
            return $driver;
        });

        $totalDrivers = $allDrivers->count();
        $activeDrivers = $allDrivers->where('is_active', true)->count();
        $passiveDrivers = $allDrivers->where('is_active', false)->count();

        $expiredDocumentCount = $allDrivers->filter(function ($driver) {
            return ($driver->resolved_document_status['priority'] ?? 'ok') === 'expired';
        })->count();

        $expiringSoonCount = $allDrivers->filter(function ($driver) {
            return in_array(($driver->resolved_document_status['priority'] ?? 'ok'), ['soon7', 'expiring'], true);
        })->count();

        return view('drivers.index', compact(
            'drivers',
            'vehicles',
            'totalDrivers',
            'activeDrivers',
            'passiveDrivers',
            'expiredDocumentCount',
            'expiringSoonCount'
        ));
    }

    public function create()
    {
        $vehicles = Vehicle::orderBy('plate')->get();

        return view('drivers.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'full_name' => 'required|string|max:255',
            'tc_no' => 'nullable|string|max:20|unique:drivers,tc_no',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'license_class' => 'nullable|string|max:50',
            'src_type' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'start_shift' => 'nullable|string|in:morning,evening',
            'base_salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Driver::create($validated);

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Personel başarıyla eklendi.');
    }

    public function show(Request $request, Driver $driver)
    {
        $driver->load([
            'vehicle',
            'payrolls',
            'documents',
        ]);

        $activeTab = $request->get('tab', 'general');

        // Eski linklerden work-documents gelirse otomatik documents'e yönlensin
        if ($activeTab === 'work-documents') {
            $activeTab = 'documents';
        }

        $driverAge = null;
        if ($driver->birth_date) {
            try {
                $driverAge = $driver->birth_date->age;
            } catch (\Throwable $e) {
                $driverAge = null;
            }
        }

        $serviceYears = null;
        if ($driver->start_date) {
            try {
                $serviceYears = $driver->start_date->diffInYears(now());
            } catch (\Throwable $e) {
                $serviceYears = null;
            }
        }

        $documents = $driver->documents
            ->sortByDesc(function ($document) {
                return optional($document->created_at)->timestamp ?? 0;
            })
            ->values()
            ->map(function ($document) use ($driver) {
                return $this->decorateDriverDocument($document, $driver);
            });

        $isImageDocument = function ($document) {
            $extension = strtolower(pathinfo((string) $document->file_path, PATHINFO_EXTENSION));
            return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
        };

        $imageDocuments = $documents->filter(function ($document) use ($isImageDocument) {
            $extension = strtolower(pathinfo((string) $document->file_path, PATHINFO_EXTENSION));
            return $isImageDocument($document) || ($extension === 'pdf' && $document->document_type === 'Kimlik ve Ehliyet');
        })->values();

        $documentDocuments = $documents->filter(function ($document) use ($isImageDocument) {
            return !$isImageDocument($document);
        })->values();

        $featuredImage = $imageDocuments->first();

        $lastPayroll = $driver->payrolls->sortByDesc('period_month')->first();
        $totalPayrollCount = $driver->payrolls->count();
        $documentCount = $documents->count();
        $imageCount = $imageDocuments->count();

        $totalNetSalary = (float) $driver->payrolls->sum('net_salary');
        $totalGrossSalary = (float) $driver->payrolls->sum('gross_salary');

        $documentStatus = 'ok';
        if ($documentDocuments->contains(fn ($doc) => $doc->alert_status === 'expired')) {
            $documentStatus = 'expired';
        } elseif ($documentDocuments->contains(fn ($doc) => $doc->alert_status === 'expiring')) {
            $documentStatus = 'expiring';
        }

        $driverDocumentTypes = $this->driverDocumentTypes;

        return view('drivers.show', compact(
            'driver',
            'activeTab',
            'documentStatus',
            'driverAge',
            'serviceYears',
            'lastPayroll',
            'totalPayrollCount',
            'documentCount',
            'imageCount',
            'documents',
            'documentDocuments',
            'imageDocuments',
            'featuredImage',
            'totalNetSalary',
            'totalGrossSalary',
            'driverDocumentTypes'
        ));
    }

    public function uploadDocument(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'document_type' => ['required', 'string', Rule::in($this->driverDocumentTypes)],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx|max:20480',
            'redirect_tab' => 'nullable|string|in:documents,images',
        ]);

        $startDate = !empty($validated['start_date']) ? Carbon::parse($validated['start_date']) : null;
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : null;
        $documentType = $validated['document_type'];

        if ($documentType === 'Adli Sicil Kaydı' && $startDate) {
            $endDate = $startDate->copy()->addMonthsNoOverflow(6);
        }

        if (in_array($documentType, ['Psikoteknik Belgesi', 'MYK Belgesi'], true) && $startDate) {
            $endDate = $startDate->copy()->addYears(5);
        }

        if (in_array($documentType, ['SRC 1 Belgesi', 'SRC 2 Belgesi'], true) && $driver->birth_date) {
            $endDate = Carbon::parse($driver->birth_date)->copy()->addYears(69);
        }

        $filePath = $request->file('file')->store('driver-documents', 'public');

        $driver->documents()->create([
            'document_type' => $documentType,
            'document_name' => $documentType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'file_path' => $filePath,
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        $redirectTab = $validated['redirect_tab'] ?? 'documents';

        return redirect()
            ->route('drivers.show', [
                'driver' => $driver->id,
                'tab' => $redirectTab,
            ])
            ->with('success', 'Belge başarıyla yüklendi.');
    }

    public function cropPhoto(Request $request, Driver $driver)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        $imageData = $request->image;
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, etc

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                return response()->json(['success' => false, 'message' => 'Geçersiz resim formatı.'], 422);
            }

            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return response()->json(['success' => false, 'message' => 'Resim verisi çözülemedi.'], 422);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Geçersiz resim verisi.'], 422);
        }

        $fileName = 'cropped_' . time() . '.' . $type;
        $filePath = 'driver-documents/' . $fileName;

        Storage::disk('public')->put($filePath, $imageData);

        $driver->documents()->create([
            'document_type' => 'Kimlik ve Ehliyet',
            'document_name' => 'Profil Fotoğrafı (Kırpılmış)',
            'file_path' => $filePath,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil fotoğrafı başarıyla oluşturuldu.',
            'redirect' => route('drivers.show', ['driver' => $driver->id, 'tab' => 'general'])
        ]);
    }

    public function deleteDocument(Driver $driver, Document $document)
    {
        if (
            $document->documentable_type !== Driver::class ||
            (int) $document->documentable_id !== (int) $driver->id
        ) {
            abort(404);
        }

        if (!empty($document->file_path) && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()
            ->back()
            ->with('success', 'Belge silindi.');
    }

    public function edit(Driver $driver)
    {
        $vehicles = Vehicle::orderBy('plate')->get();

        return view('drivers.edit', compact('driver', 'vehicles'));
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'full_name' => 'required|string|max:255',
            'tc_no' => 'nullable|string|max:20|unique:drivers,tc_no,' . $driver->id,
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'license_class' => 'nullable|string|max:50',
            'src_type' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'start_shift' => 'nullable|string|in:morning,evening',
            'base_salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $driver->update($validated);

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Personel güncellendi.');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Personel silindi.');
    }

    public function leaveWork(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'leave_date' => 'required|date',
            'leave_shift' => 'required|string|in:morning,evening,full_day',
        ]);

        $driver->update([
            'is_active' => false,
            'leave_date' => $validated['leave_date'],
            'leave_shift' => $validated['leave_shift'],
            // 'vehicle_id' => null, // Artık aracı boşa çıkarmıyoruz ki geçmiş maaş hesaplanabilsin
        ]);

        return redirect()->back()->with('success', 'Personel işten ayrılma kaydı başarıyla yapıldı.');
    }

    public function changeVehicle(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $driver->update([
            'vehicle_id' => $validated['vehicle_id'],
        ]);

        return redirect()->back()->with('success', 'Personel araç ataması başarıyla güncellendi.');
    }

    protected function resolveDriverDocumentStatus(Driver $driver): array
    {
        $documents = $driver->documents->map(function ($document) use ($driver) {
            return $this->decorateDriverDocument($document, $driver);
        });

        $nonImageDocuments = $documents->filter(function ($document) {
            $extension = strtolower(pathinfo((string) $document->file_path, PATHINFO_EXTENSION));
            return !in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
        });

        if ($nonImageDocuments->contains(fn ($document) => $document->alert_status === 'expired')) {
            return [
                'label' => 'Süresi Geçmiş',
                'class' => 'bg-rose-100 text-rose-700',
                'priority' => 'expired',
            ];
        }

        if ($nonImageDocuments->contains(fn ($document) => $document->alert_status === 'expiring')) {
            return [
                'label' => '7 Gün İçinde Bitecek',
                'class' => 'bg-orange-100 text-orange-700',
                'priority' => 'soon7',
            ];
        }

        if ($nonImageDocuments->contains(fn ($document) => $document->alert_status === 'active_soon')) {
            return [
                'label' => 'Yakında Bitecek',
                'class' => 'bg-amber-100 text-amber-700',
                'priority' => 'expiring',
            ];
        }

        return [
            'label' => 'Uygun',
            'class' => 'bg-emerald-100 text-emerald-700',
            'priority' => 'ok',
        ];
    }

    protected function decorateDriverDocument(Document $document, Driver $driver): Document
    {
        $today = now()->startOfDay();
        $sevenDaysLater = now()->copy()->addDays(7)->endOfDay();
        $thirtyDaysLater = now()->copy()->addDays(30)->endOfDay();

        $document->remaining_days = null;
        $document->alert_status = null;
        $document->alert_text = 'Süresiz belge';

        if ($document->end_date) {
            $remainingDays = $today->diffInDays(Carbon::parse($document->end_date)->startOfDay(), false);
            $document->remaining_days = $remainingDays;

            if ($remainingDays < 0) {
                $document->alert_status = 'expired';
                $document->alert_text = 'Süresi doldu';
            } elseif ($remainingDays <= 7) {
                $document->alert_status = 'expiring';
                $document->alert_text = $remainingDays . ' gün kaldı';
            } elseif (Carbon::parse($document->end_date)->between($today, $thirtyDaysLater)) {
                $document->alert_status = 'active_soon';
                $document->alert_text = $remainingDays . ' gün kaldı';
            } else {
                $document->alert_status = 'active';
                $document->alert_text = $remainingDays . ' gün kaldı';
            }
        }

        return $document;
    }

    public static function getDriverDocumentAlertsForLayout(): array
    {
        $today = now()->startOfDay();
        $sevenDaysLater = now()->copy()->addDays(7)->endOfDay();

        return Document::query()
            ->where('documentable_type', Driver::class)
            ->where('is_active', true)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<=', $sevenDaysLater)
            ->with('documentable')
            ->orderBy('end_date')
            ->get()
            ->filter(fn ($doc) => $doc->documentable)
            ->map(function ($doc) use ($today) {
                $endDate = Carbon::parse($doc->end_date)->startOfDay();
                $remainingDays = $today->diffInDays($endDate, false);

                return [
                    'document_id' => $doc->id,
                    'driver_id' => $doc->documentable_id,
                    'driver_name' => $doc->documentable?->full_name ?? 'Personel',
                    'document_type' => $doc->document_type ?: $doc->document_name,
                    'end_date' => $doc->end_date,
                    'remaining_days' => $remainingDays,
                    'status' => $remainingDays < 0 ? 'expired' : 'expiring',
                    'route' => route('drivers.show', [
                        'driver' => $doc->documentable_id,
                        'tab' => 'documents',
                    ]),
                ];
            })
            ->values()
            ->all();
    }
}
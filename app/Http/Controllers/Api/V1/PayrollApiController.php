<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\PayrollReadService;
use Illuminate\Http\Request;

class PayrollApiController extends BaseApiController
{
    protected $payrollReadService;

    public function __construct(PayrollReadService $payrollReadService)
    {
        $this->payrollReadService = $payrollReadService;
    }

    public function index(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'payrolls.view')) {
            return $this->errorResponse('Bu alanı görüntüleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $perPage = $request->input('per_page', 20);

        $paginator = $this->payrollReadService->getPayrollsPaginated($companyId, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Bordro listesi başarıyla getirildi.',
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
            ],
            'errors'  => null
        ]);
    }

    public function show(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'payrolls.view')) {
            return $this->errorResponse('Bu alanı görüntüleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $payroll = $this->payrollReadService->getPayrollDetail($companyId, $id);

        if (!$payroll) {
            return $this->errorResponse('Bordro bulunamadı.', 404);
        }

        return $this->successResponse($payroll, 'Bordro detayı getirildi.');
    }

    public function store(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'payrolls.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $validated = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'period_month' => 'required|string|max:7',
            'base_salary' => 'nullable|numeric',
            'extra_payment' => 'nullable|numeric',
            'deduction' => 'nullable|numeric',
            'advance_payment' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $this->getCompanyId();
        
        $baseSalary = (float) ($validated['base_salary'] ?? 0);
        $extraPayment = (float) ($validated['extra_payment'] ?? 0);
        $deduction = (float) ($validated['deduction'] ?? 0);
        $advancePayment = (float) ($validated['advance_payment'] ?? 0);
        
        $validated['net_salary'] = $baseSalary + $extraPayment - $deduction - $advancePayment;

        $payroll = \App\Models\Payroll::create($validated);

        return $this->successResponse($payroll, 'Bordro kaydı başarıyla eklendi.', 201);
    }

    public function update(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'payrolls.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $payroll = \App\Models\Payroll::where('company_id', $this->getCompanyId())->find($id);

        if (!$payroll) {
            return $this->errorResponse('Kayıt bulunamadı.', 404);
        }

        $validated = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'period_month' => 'required|string|max:7',
            'base_salary' => 'nullable|numeric',
            'extra_payment' => 'nullable|numeric',
            'deduction' => 'nullable|numeric',
            'advance_payment' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $baseSalary = (float) ($validated['base_salary'] ?? 0);
        $extraPayment = (float) ($validated['extra_payment'] ?? 0);
        $deduction = (float) ($validated['deduction'] ?? 0);
        $advancePayment = (float) ($validated['advance_payment'] ?? 0);
        
        $validated['net_salary'] = $baseSalary + $extraPayment - $deduction - $advancePayment;

        $payroll->update($validated);

        return $this->successResponse($payroll, 'Bordro kaydı başarıyla güncellendi.');
    }

    public function destroy(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'payrolls.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $payroll = \App\Models\Payroll::where('company_id', $this->getCompanyId())->find($id);

        if (!$payroll) {
            return $this->errorResponse('Kayıt bulunamadı.', 404);
        }

        $payroll->delete();

        return $this->successResponse(null, 'Bordro kaydı başarıyla silindi.');
    }

    public function period(Request $request, $period)
    {
        if (!$this->userHasPermission($request->user(), 'payrolls.view')) {
            return $this->errorResponse('Bu alanı görüntüleme yetkiniz yok.', 403);
        }

        try {
            [$year, $month] = explode('-', $period);
        } catch (\Exception $e) {
            return $this->errorResponse('Geçersiz dönem formatı. Beklenen: YYYY-MM', 400);
        }

        $companyId = $this->getCompanyId();
        $payrollService = new \App\Services\PayrollService();
        
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $drivers = \App\Models\Fleet\Driver::query()
            ->with(['vehicle'])
            ->where('drivers.company_id', $companyId)
            ->where(function($query) use ($startOfMonth, $endOfMonth) {
                $query->where('drivers.is_active', true)
                      ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                          $q->where('drivers.is_active', false)
                            ->where(function($sq) use ($startOfMonth, $endOfMonth) {
                                $sq->whereNull('drivers.leave_date')
                                   ->orWhere('drivers.leave_date', '>=', $startOfMonth);
                            })
                            ->where(function($sq) use ($startOfMonth, $endOfMonth) {
                                $sq->whereNull('drivers.start_date')
                                   ->orWhere('drivers.start_date', '<=', $endOfMonth);
                            });
                      });
            })
            ->leftJoin('vehicles', 'drivers.vehicle_id', '=', 'vehicles.id')
            ->orderBy('vehicles.plate', 'asc')
            ->orderBy('drivers.full_name', 'asc')
            ->select('drivers.*')
            ->get();
        
        $calculatedPayrolls = [];
        foreach ($drivers as $driver) {
            $calculation = $payrollService->calculateMonthlyPayroll($driver, (int)$month, (int)$year);
            
            $existing = \App\Models\Payroll::where('driver_id', $driver->id)
                ->where('period_month', $period)
                ->where('company_id', $companyId)
                ->first();

            $calculatedPayrolls[] = [
                'driver' => [
                    'id' => $driver->id,
                    'full_name' => $driver->full_name,
                    'tc_no' => $driver->tc_no,
                    'vehicle' => $driver->vehicle ? [
                        'id' => $driver->vehicle->id,
                        'plate' => $driver->vehicle->plate
                    ] : null,
                ],
                'calculation' => $calculation,
                'existing' => $existing
            ];
        }

        $lockStatus = \App\Models\PayrollLock::where('period', $period)->first();
        $isLocked = $lockStatus ? (bool)$lockStatus->is_locked : false;

        return $this->successResponse([
            'period' => $period,
            'is_locked' => $isLocked,
            'payrolls' => $calculatedPayrolls
        ], 'Dönem maaş verileri başarıyla getirildi.');
    }

    public function toggleLock(Request $request, $period)
    {
        $user = $request->user();
        if (!$user->isCompanyAdmin() && !$user->isSuperAdmin()) {
            return $this->errorResponse('Bu işlem için yetkiniz yok.', 403);
        }
        
        $lock = \App\Models\PayrollLock::firstOrNew(['period' => $period]);
        $lock->is_locked = !$lock->is_locked;
        $lock->save();

        return $this->successResponse([
            'is_locked' => $lock->is_locked
        ], $lock->is_locked ? 'Maaş tablosu düzenlemeye kapatıldı.' : 'Maaş tablosu düzenlemeye açıldı.');
    }

    public function updateSingle(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'payrolls.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        try {
            $driverId = $request->input('driver_id');
            $period = $request->input('period');
            $data = $request->input('data');

            // Kilit Kontrolü
            $lock = \App\Models\PayrollLock::where('period', $period)->first();
            if ($lock && $lock->is_locked) {
                return $this->errorResponse('Bu dönem kilitlenmiştir. Düzenleme yapmak için önce kilidi açmalısınız.', 403);
            }

            $net = (float)($data['base_salary'] + $data['extra_earnings'] + ($data['extra_bonus'] ?? 0)) 
                 - (float)(($data['bank_payment'] ?? 0) + ($data['traffic_penalty'] ?? 0) + ($data['advance_payment'] ?? 0) + ($data['deduction'] ?? 0));

            $companyId = $this->getCompanyId();

            $payroll = \App\Models\Payroll::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'driver_id' => $driverId,
                    'period_month' => $period,
                ],
                [
                    'base_salary' => $data['base_salary'],
                    'extra_payment' => $data['extra_earnings'],
                    'bank_payment' => $data['bank_payment'] ?? 0,
                    'traffic_penalty' => $data['traffic_penalty'] ?? 0,
                    'advance_payment' => $data['advance_payment'] ?? 0,
                    'deduction' => $data['deduction'] ?? 0,
                    'deduction_notes' => $data['deduction_notes'] ?? null,
                    'extra_bonus' => $data['extra_bonus'] ?? 0,
                    'extra_notes' => $data['extra_notes'] ?? null,
                    'net_salary' => $net,
                    'is_active' => true,
                ]
            );

            return $this->successResponse(['net' => $net, 'payroll' => $payroll], 'Bordro başarıyla güncellendi.');
        } catch (\Exception $e) {
            return $this->errorResponse('Bordro güncellenirken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    public function options(Request $request)
    {
        $drivers = \App\Models\Fleet\Driver::where('company_id', $this->getCompanyId())
            ->where('is_active', true)
            ->get(['id', 'full_name as name']);

        return $this->successResponse([
            'drivers' => $drivers,
        ], 'Form seçenekleri başarıyla getirildi.');
    }
}

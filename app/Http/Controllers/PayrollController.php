<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Fleet\Driver;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('payrolls.view'), 403);

        $period = $request->get('period', now()->format('Y-m'));
        [$year, $month] = explode('-', $period);

        $payrollService = new PayrollService();
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $drivers = Driver::query()
            ->with(['vehicle'])
            ->where(function($query) use ($startOfMonth, $endOfMonth) {
                $query->where('drivers.is_active', true)
                      ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                          $q->where('drivers.is_active', false)
                            ->where(function($sq) use ($startOfMonth, $endOfMonth) {
                                // Ay içinde en az bir gün çalışmış olması lazım
                                $sq->whereNull('leave_date')
                                   ->orWhere('leave_date', '>=', $startOfMonth);
                            })
                            ->where(function($sq) use ($startOfMonth, $endOfMonth) {
                                $sq->whereNull('start_date')
                                   ->orWhere('start_date', '<=', $endOfMonth);
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
            
            $existing = Payroll::where('driver_id', $driver->id)
                ->where('period_month', $period)
                ->first();

            $calculatedPayrolls[] = [
                'driver' => $driver,
                'calculation' => $calculation,
                'existing' => $existing
            ];
        }

        // Kilit Kontrolü
        $lockStatus = \App\Models\PayrollLock::where('period', $period)->first();
        $isLocked = $lockStatus ? $lockStatus->is_locked : false;

        // Otomatik Hatırlatıcı Mantığı (Admin ise, ayın 5'i geçtiyse ve geçen ay kilitli değilse)
        $shouldAskLock = false;
        $prevPeriod = now()->subMonth()->format('Y-m');
        if ((auth()->user()->isCompanyAdmin() || auth()->user()->isSuperAdmin()) && now()->day >= 5) {
            $prevLock = \App\Models\PayrollLock::where('period', $prevPeriod)->first();
            if (!$prevLock || !$prevLock->is_locked) {
                $shouldAskLock = true;
            }
        }

        return view('payrolls.index', compact('calculatedPayrolls', 'period', 'isLocked', 'shouldAskLock', 'prevPeriod'));
    }

    public function toggleLock(Request $request)
    {
        if (!auth()->user()->isCompanyAdmin() && !auth()->user()->isSuperAdmin()) {
            return response()->json(['error' => 'Bu işlem için yetkiniz yok.'], 403);
        }
        
        $period = $request->period;
        $lock = \App\Models\PayrollLock::firstOrNew(['period' => $period]);
        $lock->is_locked = !$lock->is_locked;
        $lock->save();

        return response()->json([
            'status' => 'success', 
            'is_locked' => $lock->is_locked,
            'message' => $lock->is_locked ? 'Maaş tablosu düzenlemeye kapatıldı.' : 'Maaş tablosu düzenlemeye açıldı.'
        ]);
    }

    public function bulkStore(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('payrolls.create') || auth()->user()->hasPermission('payrolls.edit'), 403);

        $period = $request->get('period');

        // Kilit Kontrolü
        $lock = \App\Models\PayrollLock::where('period', $period)->first();
        if ($lock && $lock->is_locked) {
            return response()->json(['error' => 'Bu dönem kilitlenmiştir. Düzenleme yapmak için önce kilidi açmalısınız.'], 403);
        }

        $data = $request->get('payrolls', []);

        foreach ($data as $driverId => $payrollData) {
            $base = (float)($payrollData['base_salary'] ?? 0);
            $trips = (float)($payrollData['extra_earnings'] ?? 0);
            $extraBonus = (float)($payrollData['extra_bonus'] ?? 0);
            
            $bank = (float)($payrollData['bank_payment'] ?? 0);
            $penalty = (float)($payrollData['traffic_penalty'] ?? 0);
            $advance = (float)($payrollData['advance_payment'] ?? 0);
            $deduction = (float)($payrollData['deduction'] ?? 0);

            // Net Ödenecek = (Ana Maaş + Sefer Hakediş + Ekstra) - (Banka + Ceza + Avans + Kesinti)
            $net = ($base + $trips + $extraBonus) - ($bank + $penalty + $advance + $deduction);

            Payroll::updateOrCreate(
                [
                    'driver_id' => $driverId,
                    'period_month' => $period,
                ],
                [
                    'company_id' => auth()->user()->company_id,
                    'base_salary' => $base,
                    'extra_payment' => $trips,
                    'extra_bonus' => $extraBonus,
                    'extra_notes' => $payrollData['extra_notes'] ?? null,
                    'bank_payment' => $bank,
                    'traffic_penalty' => $penalty,
                    'advance_payment' => $advance,
                    'deduction' => $deduction,
                    'deduction_notes' => $payrollData['deduction_notes'] ?? null,
                    'net_salary' => $net,
                    'is_active' => true,
                ]
            );
        }

        return redirect()->back()->with('success', "$period dönemi maaş ve finansal kayıtları başarıyla güncellendi.");
    }

    public function updateSingle(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('payrolls.edit'), 403);

        try {
            $driverId = $request->input('driver_id');
            $period = $request->input('period');
            $data = $request->input('data');

            // Kilit Kontrolü
            $lock = \App\Models\PayrollLock::where('period', $period)->first();
            if ($lock && $lock->is_locked) {
                return response()->json(['error' => 'Bu dönem kilitlenmiştir. Düzenleme yapmak için önce kilidi açmalısınız.'], 403);
            }

            $net = (float)($data['base_salary'] + $data['extra_earnings'] + ($data['extra_bonus'] ?? 0)) 
                 - (float)(($data['bank_payment'] ?? 0) + ($data['traffic_penalty'] ?? 0) + ($data['advance_payment'] ?? 0) + ($data['deduction'] ?? 0));

            Payroll::updateOrCreate(
                [
                    'company_id' => auth()->user()->company_id,
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

            return response()->json(['success' => true, 'net' => $net]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function bulkReport(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('payrolls.view'), 403);

        $driverIds = explode(',', $request->input('driver_ids'));
        $period = $request->input('period');
        
        $drivers = Driver::whereIn('id', $driverIds)->get();
        $payrollService = new \App\Services\PayrollService();
        
        $reports = [];
        foreach ($drivers as $driver) {
            $periodDate = \Carbon\Carbon::parse($period);
            $reports[] = [
                'driver' => $driver,
                'report' => $payrollService->calculateMonthlyPayroll($driver, $periodDate->month, $periodDate->year)
            ];
        }
        
        return view('payrolls.print-bulk', compact('reports', 'period'));
    }

    public function showReport($driverId, $period)
    {
        abort_unless(auth()->user()->hasPermission('payrolls.view'), 403);

        $driver = Driver::findOrFail($driverId);
        [$year, $month] = explode('-', $period);
        $payrollService = new PayrollService();
        $report = $payrollService->calculateMonthlyPayroll($driver, (int)$month, (int)$year);

        return view('payrolls.report', compact('driver', 'period', 'report'));
    }

    public function printSingle($driverId, $period)
    {
        abort_unless(auth()->user()->hasPermission('payrolls.view'), 403);

        $driver = Driver::findOrFail($driverId);
        [$year, $month] = explode('-', $period);
        $payrollService = new PayrollService();
        $report = $payrollService->calculateMonthlyPayroll($driver, (int)$month, (int)$year);

        return view('payrolls.print-single', compact('driver', 'period', 'report'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('payrolls.create'), 403);

        $drivers = Driver::orderBy('full_name')->get();

        return view('payrolls.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('payrolls.create'), 403);

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

        Payroll::create($validated);

        return redirect()->route('payrolls.index')->with('success', 'Maaş kaydı başarıyla eklendi.');
    }

    public function show(Payroll $payroll)
    {
        abort_unless(auth()->user()->hasPermission('payrolls.view'), 403);

        return view('payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        abort_unless(auth()->user()->hasPermission('payrolls.edit'), 403);

        $drivers = Driver::orderBy('full_name')->get();

        return view('payrolls.edit', compact('payroll', 'drivers'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        abort_unless(auth()->user()->hasPermission('payrolls.edit'), 403);

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

        return redirect()->route('payrolls.index')->with('success', 'Maaş kaydı güncellendi.');
    }

    public function destroy(Payroll $payroll)
    {
        abort_unless(auth()->user()->hasPermission('payrolls.delete'), 403);

        $payroll->delete();

        return redirect()->route('payrolls.index')->with('success', 'Maaş kaydı silindi.');
    }
}
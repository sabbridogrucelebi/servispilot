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
        $period = $request->get('period', now()->format('Y-m'));
        [$year, $month] = explode('-', $period);

        $payrollService = new PayrollService();
        $drivers = Driver::query()
            ->with(['vehicle'])
            ->where('is_active', true)
            ->leftJoin('vehicles', 'drivers.vehicle_id', '=', 'vehicles.id')
            ->orderBy('vehicles.plate', 'asc')
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

        return view('payrolls.index', compact('calculatedPayrolls', 'period'));
    }

    public function bulkStore(Request $request)
    {
        $period = $request->get('period');
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
        try {
            $driverId = $request->input('driver_id');
            $period = $request->input('period');
            $data = $request->input('data');

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

    public function showReport($driverId, $period)
    {
        $driver = Driver::findOrFail($driverId);
        [$year, $month] = explode('-', $period);

        $payrollService = new PayrollService();
        $report = $payrollService->calculateMonthlyPayroll($driver, (int)$month, (int)$year);

        return view('payrolls.report', compact('driver', 'period', 'report'));
    }

    public function create()
    {
        $drivers = Driver::orderBy('full_name')->get();

        return view('payrolls.create', compact('drivers'));
    }

    public function store(Request $request)
    {
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
        return view('payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $drivers = Driver::orderBy('full_name')->get();

        return view('payrolls.edit', compact('payroll', 'drivers'));
    }

    public function update(Request $request, Payroll $payroll)
    {
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
        $payroll->delete();

        return redirect()->route('payrolls.index')->with('success', 'Maaş kaydı silindi.');
    }
}
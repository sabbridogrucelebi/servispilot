<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Fleet\Driver;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = Payroll::with('driver')
            ->orderByDesc('period_month')
            ->latest()
            ->get();

        return view('payrolls.index', compact('payrolls'));
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
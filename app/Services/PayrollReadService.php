<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\PayrollLock;

class PayrollReadService
{
    /**
     * V1 API için sayfalı bordro okuma servisi
     */
    public function getPayrollsPaginated($companyId, $perPage = 20)
    {
        $perPage = min((int) $perPage, 50);
        if ($perPage < 1) $perPage = 20;

        $payrolls = Payroll::with('driver:id,full_name')
            ->where('company_id', $companyId)
            ->latest('period_month')
            ->latest('id')
            ->paginate($perPage);

        // N+1 önlemek için lock durumlarını toplu çekeriz
        $periods = $payrolls->pluck('period_month')->unique();
        $locks = PayrollLock::whereIn('period', $periods)->pluck('is_locked', 'period');

        $payrolls->getCollection()->transform(function ($payroll) use ($locks) {
            return [
                'id' => $payroll->id,
                'driver_name' => $payroll->driver ? $payroll->driver->full_name : 'Bilinmeyen Sürücü',
                'period_month' => $payroll->period_month,
                'period_human' => tap(\Carbon\Carbon::createFromFormat('Y-m', $payroll->period_month), fn($dt) => \Carbon\Carbon::setLocale('tr'))->translatedFormat('F Y'),
                'is_locked' => $locks->get($payroll->period_month, false),
                'net_salary' => (float) $payroll->net_salary,
                'base_salary' => (float) $payroll->base_salary,
                'extra_payment' => (float) $payroll->extra_payment,
                'deduction' => (float) $payroll->deduction,
                'advance_payment' => (float) $payroll->advance_payment,
            ];
        });

        return $payrolls;
    }

    /**
     * V1 API için tekil bordro detay servisi
     */
    public function getPayrollDetail($companyId, $payrollId)
    {
        $payroll = Payroll::with('driver')
            ->where('company_id', $companyId)
            ->where('id', $payrollId)
            ->first();

        if ($payroll) {
            $lockStatus = PayrollLock::where('period', $payroll->period_month)->first();
            $payroll->is_locked = $lockStatus ? (bool)$lockStatus->is_locked : false;
            $payroll->period_human = tap(\Carbon\Carbon::createFromFormat('Y-m', $payroll->period_month), fn($dt) => \Carbon\Carbon::setLocale('tr'))->translatedFormat('F Y');
            
            // Accessor eklentileri (Varsa) API formatı için eklenebilir, şimdilik manuel dönüştürmeye gerek yok model otomatik döner
        }

        return $payroll;
    }
}

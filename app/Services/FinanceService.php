<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\Fuel;
use App\Models\Payroll;
use App\Models\PayrollLock;
use App\Models\TrafficPenalty;
use App\Models\VehicleMaintenance;
use Carbon\Carbon;

class FinanceService
{
    /**
     * İlgili ayın genel finans özetini getirir. (Web paneliyle aynı hesap mantığı)
     */
    public function getMonthlySummary($companyId, $period = null)
    {
        if (!$period) {
            $period = now()->format('Y-m');
        }

        try {
            $date = Carbon::createFromFormat('Y-m', $period);
            $startDate = $date->startOfMonth()->format('Y-m-d');
            $endDate = $date->endOfMonth()->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Geçersiz dönem formatı. Beklenen: YYYY-MM');
        }

        $lockStatus = PayrollLock::where('period', $period)->first();
        $isLocked = $lockStatus ? (bool)$lockStatus->is_locked : false;

        // DB Aggregate ile Performanslı Hesaplamalar (company_id filtresi ile tam izolasyon)
        
        // 1. Gelir (Seferler - Web paneldeki mantık: whereBetween trip_date, status filtresi yok)
        $tripIncome = (float) Trip::where('company_id', $companyId)
            ->whereBetween('trip_date', [$startDate, $endDate])
            ->sum('trip_price');

        // 2. Resmi Giderler (Yakıt + Maaş)
        $fuelCost = (float) Fuel::where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('total_cost');

        $salaryCost = (float) Payroll::where('company_id', $companyId)
            ->where('period_month', $period)
            ->sum('net_salary');

        // 3. Ekstra Operasyonel Giderler (Bakım + Ceza)
        $maintenanceCost = (float) VehicleMaintenance::where('company_id', $companyId)
            ->whereBetween('maintenance_date', [$startDate, $endDate])
            ->sum('cost');

        $penaltyCost = (float) TrafficPenalty::where('company_id', $companyId)
            ->whereBetween('penalty_date', [$startDate, $endDate])
            ->sum('penalty_amount');

        // Ana Hesaplamalar (Web ile tam uyum)
        $totalExpenses = $fuelCost + $salaryCost;
        $netProfit = $tripIncome - $totalExpenses;

        // Genişletilmiş Hesaplamalar
        $operationalTotalExpenses = $totalExpenses + $maintenanceCost + $penaltyCost;
        $operationalNetProfit = $tripIncome - $operationalTotalExpenses;

        return [
            'period' => $period,
            'period_human' => tap($date, fn($dt) => \Carbon\Carbon::setLocale('tr'))->translatedFormat('F Y'),
            'is_locked' => $isLocked,
            
            'total_income' => $tripIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            
            'expenses_detail' => [
                'fuels' => $fuelCost,
                'payrolls' => $salaryCost,
            ],
            
            'additional_expenses' => [
                'maintenances' => $maintenanceCost,
                'penalties' => $penaltyCost,
            ],
            
            'operational_total_expenses' => $operationalTotalExpenses,
            'operational_net_profit' => $operationalNetProfit,
        ];
    }
}

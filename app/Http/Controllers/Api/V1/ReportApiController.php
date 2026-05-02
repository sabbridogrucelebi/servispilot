<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Fuel;
use App\Models\Payroll;
use App\Models\VehicleMaintenance;
use App\Models\TrafficPenalty;
use Illuminate\Support\Facades\DB;

class ReportApiController extends BaseApiController
{
    public function summary(Request $request)
    {
        $user = $request->user();
        
        if (!$this->userHasPermission($user, 'reports.view')) {
            return $this->errorResponse('Raporları görüntüleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Income (Trips)
        $tripIncome = Trip::where('company_id', $companyId)
            ->whereBetween('trip_date', [$startDate, $endDate])
            ->sum('trip_price');

        // Expenses
        $fuelExpense = Fuel::where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('total_cost');

        $periodMonthStart = substr($startDate, 0, 7);
        $periodMonthEnd = substr($endDate, 0, 7);
        
        $payrollExpense = Payroll::where('company_id', $companyId)
            ->whereBetween('period_month', [$periodMonthStart, $periodMonthEnd])
            ->sum('net_salary');

        $maintenanceExpense = VehicleMaintenance::where('company_id', $companyId)
            ->whereBetween('service_date', [$startDate, $endDate])
            ->sum('amount');

        $penaltyExpense = TrafficPenalty::where('company_id', $companyId)
            ->whereBetween('penalty_date', [$startDate, $endDate])
            ->sum('penalty_amount');

        $totalExpense = $fuelExpense + $payrollExpense + $maintenanceExpense + $penaltyExpense;
        $netProfit = $tripIncome - $totalExpense;

        // 6 Months Trend
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->translatedFormat('M Y');
            $periodStr = $monthStart->format('Y-m');

            $mInc = Trip::where('company_id', $companyId)
                ->whereBetween('trip_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                ->sum('trip_price');

            $mExpF = Fuel::where('company_id', $companyId)
                ->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                ->sum('total_cost');
            
            $mExpP = Payroll::where('company_id', $companyId)
                ->where('period_month', $periodStr)
                ->sum('net_salary');

            $mExpM = VehicleMaintenance::where('company_id', $companyId)
                ->whereBetween('service_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                ->sum('amount');

            $mExpPn = TrafficPenalty::where('company_id', $companyId)
                ->whereBetween('penalty_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                ->sum('penalty_amount');

            $mExpTotal = $mExpF + $mExpP + $mExpM + $mExpPn;

            $trend[] = [
                'month' => $monthLabel,
                'income' => (float)$mInc,
                'expense' => (float)$mExpTotal,
                'profit' => (float)($mInc - $mExpTotal)
            ];
        }

        return $this->successResponse([
            'summary' => [
                'income' => (float)$tripIncome,
                'expense' => (float)$totalExpense,
                'profit' => (float)$netProfit,
                'breakdown' => [
                    'fuel' => (float)$fuelExpense,
                    'payroll' => (float)$payrollExpense,
                    'maintenance' => (float)$maintenanceExpense,
                    'penalty' => (float)$penaltyExpense,
                ]
            ],
            'trend' => $trend,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ], 'Rapor özeti getirildi.');
    }
}

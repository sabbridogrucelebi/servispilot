<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\FinanceService;
use Illuminate\Http\Request;

class FinanceApiController extends BaseApiController
{
    protected $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * Genel Finansal Özet (Reports yetkisi gerektirir)
     */
    public function summary(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'reports.view')) {
            return $this->errorResponse('Bu alanı görüntüleme yetkiniz yok.', 403);
        }

        $request->validate([
            'period' => 'nullable|date_format:Y-m'
        ]);

        $companyId = $this->getCompanyId();
        $period = $request->input('period', now()->format('Y-m'));

        try {
            $summary = $this->financeService->getMonthlySummary($companyId, $period);
            
            $user = $request->user();
            
            // --- RESMİ GİDERLER YETKİ KONTROLÜ ---
            $hasFuelView = $user->can('fuels.view');
            $hasPayrollView = $user->can('payrolls.view');
            
            if (!$hasFuelView) {
                $summary['expenses_detail']['fuels'] = null;
            }
            if (!$hasPayrollView) {
                $summary['expenses_detail']['payrolls'] = null;
            }
            
            // Gizli giderin total üzerinden bulunmasını engelleme
            if (!$hasFuelView || !$hasPayrollView) {
                $summary['total_expenses'] = null;
                $summary['net_profit'] = null;
            }

            // --- EKSTRA GİDERLER YETKİ KONTROLÜ ---
            $hasMaintView = $user->can('maintenances.view');
            $hasPenaltyView = $user->can('penalties.view');

            if (!$hasMaintView) {
                $summary['additional_expenses']['maintenances'] = null;
            }
            if (!$hasPenaltyView) {
                $summary['additional_expenses']['penalties'] = null;
            }

            // Operasyonel toplamın yetkisiz hesaplanmasını engelleme
            if (!$hasMaintView || !$hasPenaltyView || !$hasFuelView || !$hasPayrollView) {
                $summary['operational_total_expenses'] = null;
                $summary['operational_net_profit'] = null;
            }

            return $this->successResponse($summary, 'Finansal özet getirildi.');
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}

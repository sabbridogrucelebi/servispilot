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
}

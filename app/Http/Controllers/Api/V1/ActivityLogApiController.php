<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ActivityLogApiController extends BaseApiController
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Logları listeler (Company Admin ve logs.view şartıyla)
     */
    public function index(Request $request)
    {
        // 1. Web paneldeki gibi isCompanyAdmin zorunluluğu
        if (!method_exists($request->user(), 'isCompanyAdmin') || !$request->user()->isCompanyAdmin()) {
            return response()->json(['success' => false, 'message' => 'Bu alanı görüntüleme yetkiniz yok.', 'data' => null, 'errors' => null], 403);
        }

        // 2. Permission zorunluluğu
        if (!$this->userHasPermission($request->user(), 'logs.view')) {
            return response()->json(['success' => false, 'message' => 'Bu alanı görüntüleme yetkiniz yok.', 'data' => null, 'errors' => null], 403);
        }

        $companyId = $this->getCompanyId();
        $perPage = $request->input('per_page', 20);

        $filters = $request->only(['module', 'action', 'user_id', 'from', 'to', 'search']);

        $paginator = $this->activityLogService->getLogsPaginated($companyId, $filters, $perPage);

        // Standart V1 Pagination Response
        return response()->json([
            'success' => true,
            'message' => 'Aktivite kayıtları başarıyla getirildi.',
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
}

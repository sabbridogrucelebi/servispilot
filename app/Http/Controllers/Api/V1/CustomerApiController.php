<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerApiController extends BaseApiController
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Müşterileri listeler
     */
    public function index(Request $request)
    {
        // Yetki kontrolü
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        $customers = $this->customerService->getCustomerList($companyId);

        return $this->successResponse($customers, 'Müşteri listesi başarıyla getirildi.');
    }

    /**
     * Tek bir müşterinin detaylarını getirir
     */
    public function show(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        $customer = $this->customerService->getCustomerDetail($companyId, $id);

        if (!$customer) {
            return $this->errorResponse('Müşteri bulunamadı.', 404);
        }

        return $this->successResponse($customer, 'Müşteri detayları başarıyla getirildi.');
    }
}

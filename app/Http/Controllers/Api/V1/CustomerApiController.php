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
    /**
     * Yeni müşteri ekler
     */
    public function store(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'customers.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $validated = $request->validate([
            'customer_type' => 'required|string|max:100',
            'company_name' => 'required|string|max:255',
            'company_title' => 'nullable|string|max:255',
            'authorized_person' => 'nullable|string|max:255',
            'authorized_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'vat_rate' => 'required|numeric',
            'withholding_rate' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $validated['company_id'] = $this->getCompanyId();

        $customer = \App\Models\Customer::create($validated);

        return $this->successResponse($customer, 'Müşteri başarıyla eklendi.', 201);
    }

    /**
     * Müşteri günceller
     */
    public function update(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $customer = \App\Models\Customer::where('company_id', $this->getCompanyId())->find($id);

        if (!$customer) {
            return $this->errorResponse('Müşteri bulunamadı.', 404);
        }

        $validated = $request->validate([
            'customer_type' => 'required|string|max:100',
            'company_name' => 'required|string|max:255',
            'company_title' => 'nullable|string|max:255',
            'authorized_person' => 'nullable|string|max:255',
            'authorized_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'vat_rate' => 'required|numeric',
            'withholding_rate' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $customer->update($validated);

        return $this->successResponse($customer, 'Müşteri başarıyla güncellendi.');
    }

    /**
     * Müşteri siler
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $customer = \App\Models\Customer::where('company_id', $this->getCompanyId())->find($id);

        if (!$customer) {
            return $this->errorResponse('Müşteri bulunamadı.', 404);
        }

        $customer->delete();

        return $this->successResponse(null, 'Müşteri başarıyla silindi.');
    }
    /**
     * Müşteri fatura özeti
     */
    public function invoices(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $customer = \App\Models\Customer::where('company_id', $this->getCompanyId())->find($id);

        if (!$customer) {
            return $this->errorResponse('Müşteri bulunamadı.', 404);
        }

        $selectedMonth = $request->get('month', now()->month);
        $selectedYear = $request->get('year', now()->year);

        $startOfMonth = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $subtotal = \App\Models\Trip::query()
            ->whereHas('serviceRoute', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->whereDate('trip_date', '>=', $startOfMonth->toDateString())
            ->whereDate('trip_date', '<=', $endOfMonth->toDateString())
            ->sum('trip_price');

        $vatRate = (float) ($customer->vat_rate ?? 0);
        $vatAmount = $subtotal * ($vatRate / 100);
        $invoiceTotal = $subtotal + $vatAmount;

        $withholdingAmount = 0;
        $withholdingRate = $customer->withholding_rate;

        if ($withholdingRate && str_contains($withholdingRate, '/')) {
            [$numerator, $denominator] = array_pad(explode('/', $withholdingRate), 2, null);
            $numerator = (float) $numerator;
            $denominator = (float) $denominator;

            if ($numerator > 0 && $denominator > 0) {
                $withholdingAmount = $vatAmount * ($numerator / $denominator);
            }
        }

        $netTotal = $invoiceTotal - $withholdingAmount;

        $invoiceSummary = [
            'subtotal' => $subtotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'withholding_rate' => $withholdingRate,
            'withholding_amount' => $withholdingAmount,
            'net_total' => $netTotal,
            'month' => $selectedMonth,
            'year' => $selectedYear
        ];

        return $this->successResponse($invoiceSummary, 'Fatura özeti getirildi.');
    }
}

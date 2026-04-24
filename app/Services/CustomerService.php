<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    /**
     * Şirkete ait tüm müşterileri listeler.
     */
    public function getCustomerList($companyId)
    {
        return Customer::where('company_id', $companyId)
            ->orderBy('company_name')
            ->get();
    }

    /**
     * Belirli bir müşterinin tüm detaylarını, sözleşmelerini ve rotalarını getirir.
     */
    public function getCustomerDetail($companyId, $customerId)
    {
        return Customer::where('company_id', $companyId)
            ->where('id', $customerId)
            ->with([
                'contracts' => function($q) {
                    $q->orderByDesc('end_date');
                },
                'serviceRoutes' => function($q) {
                    $q->latest();
                }
            ])
            ->first();
    }
}

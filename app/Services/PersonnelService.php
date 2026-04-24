<?php

namespace App\Services;

use App\Models\Fleet\Driver;

class PersonnelService
{
    /**
     * Şirkete ait tüm personelleri (sürücüleri) listeler.
     */
    public function getPersonnelList($companyId)
    {
        return Driver::where('company_id', $companyId)
            ->with('vehicle:id,plate')
            ->orderBy('full_name')
            ->get();
    }

    /**
     * Belirli bir personelin tüm detaylarını, aracını, belgelerini ve son bordrolarını getirir.
     */
    public function getPersonnelDetail($companyId, $driverId)
    {
        return Driver::where('company_id', $companyId)
            ->where('id', $driverId)
            ->with([
                'vehicle', 
                'documents', 
                'payrolls' => function($q) {
                    $q->latest('period_month')->take(5); // Sadece son 5 bordroyu getir
                }
            ])
            ->first();
    }
}

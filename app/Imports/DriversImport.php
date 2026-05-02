<?php

namespace App\Imports;

use App\Models\Fleet\Driver;
use App\Models\Fleet\Vehicle;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DriversImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Eğer hiçbir ad soyad girilmemişse kaydetmiyoruz, çünkü isim olmadan personel olmaz.
        if (empty($row['ad_soyad'])) {
            return null;
        }

        // TC Kimlik tekrarını kontrol edelim
        if (!empty($row['tc_kimlik_no'])) {
            $existing = Driver::where('company_id', auth()->user()->company_id)
                              ->where('tc_no', $row['tc_kimlik_no'])
                              ->first();
            if ($existing) {
                return null;
            }
        }

        // Araç bulma
        $vehicleId = null;
        if (!empty($row['bagli_arac_plaka'])) {
            $plate = str_replace(' ', '', mb_strtoupper($row['bagli_arac_plaka']));
            $vehicle = Vehicle::where('company_id', auth()->user()->company_id)
                              ->whereRaw("REPLACE(UPPER(plate), ' ', '') = ?", [$plate])
                              ->first();
            if ($vehicle) {
                $vehicleId = $vehicle->id;
            }
        }

        return new Driver([
            'company_id'    => auth()->user()->company_id,
            'vehicle_id'    => $vehicleId,
            'full_name'     => $row['ad_soyad'],
            'tc_no'         => $row['tc_kimlik_no'] ?? null,
            'phone'         => $row['telefon'] ?? null,
            'email'         => $row['eposta'] ?? null,
            'birth_date'    => $this->parseDate($row['dogum_tarihi'] ?? null),
            'start_date'    => $this->parseDate($row['ise_giris_tarihi'] ?? null),
            'base_salary'   => isset($row['maas']) ? floatval($row['maas']) : null,
            'license_class' => $row['ehliyet_sinifi'] ?? null,
            'src_type'      => $row['src_turu'] ?? null,
            'address'       => $row['adres'] ?? null,
            'notes'         => $row['notlar'] ?? null,
            'is_active'     => true,
        ]);
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject($value));
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}

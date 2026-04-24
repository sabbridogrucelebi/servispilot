<?php

namespace App\Imports;

use App\Models\Fleet\Vehicle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class VehiclesImport implements ToCollection, WithHeadingRow
{
    private function parseDate($value)
    {
        if (empty($value)) return null;
        
        // If it's numeric, it might be an Excel serialized date
        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
        
        // Otherwise try to parse as string
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function collection(Collection $rows)
    {
        $companyId = auth()->user()->company_id;

        foreach ($rows as $row) {
            $plate = trim($row['plaka'] ?? '');
            
            if (empty($plate)) {
                continue; // Skip empty plates
            }

            // Normalization
            $plate = strtoupper(str_replace(' ', '', $plate));

            // Check if vehicle already exists in this company
            $vehicle = Vehicle::where('company_id', $companyId)
                              ->where('plate', $plate)
                              ->first();

            if (!$vehicle) {
                $vehicle = new Vehicle();
                $vehicle->company_id = $companyId;
                $vehicle->plate = $plate;
                $vehicle->public_image_upload_token = Str::random(40);
                $vehicle->is_active = true;
            }

            if (isset($row['marka']) && trim($row['marka']) !== '') $vehicle->brand = trim($row['marka']);
            if (isset($row['model']) && trim($row['model']) !== '') $vehicle->model = trim($row['model']);
            if (isset($row['arac_tipi']) && trim($row['arac_tipi']) !== '') $vehicle->vehicle_type = trim($row['arac_tipi']);
            if (isset($row['paket']) && trim($row['paket']) !== '') $vehicle->vehicle_package = trim($row['paket']);
            
            if (isset($row['model_yili']) && trim($row['model_yili']) !== '') $vehicle->model_year = (int)trim($row['model_yili']);
            if (isset($row['koltuk_sayisi']) && trim($row['koltuk_sayisi']) !== '') $vehicle->seat_count = (int)trim($row['koltuk_sayisi']);
            if (isset($row['guncel_km']) && trim($row['guncel_km']) !== '') $vehicle->current_km = (int)trim($row['guncel_km']);

            if (isset($row['vites_tipi']) && trim($row['vites_tipi']) !== '') $vehicle->gear_type = trim($row['vites_tipi']);
            if (isset($row['yakit_tipi']) && trim($row['yakit_tipi']) !== '') $vehicle->fuel_type = trim($row['yakit_tipi']);
            if (isset($row['renk']) && trim($row['renk']) !== '') $vehicle->color = trim($row['renk']);
            if (isset($row['diger_renk']) && trim($row['diger_renk']) !== '') $vehicle->other_color = trim($row['diger_renk']);
            if (isset($row['motor_no']) && trim($row['motor_no']) !== '') $vehicle->engine_no = trim($row['motor_no']);
            if (isset($row['sasi_no']) && trim($row['sasi_no']) !== '') $vehicle->chassis_no = trim($row['sasi_no']);
            if (isset($row['ruhsat_seri_no']) && trim($row['ruhsat_seri_no']) !== '') $vehicle->license_serial_no = trim($row['ruhsat_seri_no']);
            if (isset($row['ruhsat_sahibi']) && trim($row['ruhsat_sahibi']) !== '') $vehicle->license_owner = trim($row['ruhsat_sahibi']);
            if (isset($row['tc_vkn']) && trim($row['tc_vkn']) !== '') $vehicle->owner_tax_or_tc_no = trim($row['tc_vkn']);
            if (isset($row['notlar']) && trim($row['notlar']) !== '') $vehicle->notes = trim($row['notlar']);

            // Dates
            if (isset($row['kayit_tarihi']) && trim($row['kayit_tarihi']) !== '') $vehicle->registration_date = $this->parseDate($row['kayit_tarihi']);
            if (isset($row['muayene_tarihi']) && trim($row['muayene_tarihi']) !== '') $vehicle->inspection_date = $this->parseDate($row['muayene_tarihi']);
            if (isset($row['egzoz_tarihi']) && trim($row['egzoz_tarihi']) !== '') $vehicle->exhaust_date = $this->parseDate($row['egzoz_tarihi']);
            if (isset($row['trafik_sigortasi_bitis']) && trim($row['trafik_sigortasi_bitis']) !== '') $vehicle->insurance_end_date = $this->parseDate($row['trafik_sigortasi_bitis']);
            if (isset($row['kasko_bitis']) && trim($row['kasko_bitis']) !== '') $vehicle->kasko_end_date = $this->parseDate($row['kasko_bitis']);

            // Save and let LogsActivity log it!
            $vehicle->save();
        }
    }
}

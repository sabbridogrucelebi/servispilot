<?php

namespace App\Imports;

use App\Models\Fleet\Vehicle;
use App\Models\VehicleMaintenance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class MaintenancesImport implements ToCollection, WithHeadingRow
{
    private function parseDate($value)
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

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
                continue;
            }

            $plate = strtoupper(str_replace(' ', '', $plate));

            // Araç bul
            $vehicle = Vehicle::where('company_id', $companyId)
                              ->where('plate', $plate)
                              ->first();

            if (!$vehicle) {
                continue; // Araç yoksa bu satırı atla
            }

            VehicleMaintenance::create([
                'company_id'        => $companyId,
                'vehicle_id'        => $vehicle->id,
                'created_by'        => auth()->id(),
                'maintenance_type'  => trim($row['bakim_turu'] ?? '') ?: null,
                'title'             => trim($row['bakim_adi'] ?? '') ?: null,
                'service_name'      => trim($row['servis_adi'] ?? '') ?: null,
                'service_date'      => $this->parseDate($row['servis_tarihi'] ?? null),
                'km'                => !empty($row['km']) ? (int) $row['km'] : null,
                'amount'            => !empty($row['tutar']) ? (float) str_replace(',', '.', $row['tutar']) : 0,
                'next_service_date' => $this->parseDate($row['sonraki_servis_tarihi'] ?? null),
                'next_service_km'   => !empty($row['sonraki_servis_km']) ? (int) $row['sonraki_servis_km'] : null,
                'description'       => trim($row['aciklama'] ?? '') ?: null,
                'status'            => 'completed',
            ]);
        }
    }
}

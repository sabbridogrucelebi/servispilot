<?php

namespace App\Imports;

use App\Models\Fuel;
use App\Models\FuelStation;
use App\Models\Fleet\Vehicle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class FuelsImport implements ToCollection
{
    public array $errors = [];
    public int $importedCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows->skip(1) as $index => $row) {
            try {
                $plate = trim((string) ($row[0] ?? ''));
                $date = $row[1] ?? null;
                $km = $row[2] ?? null;
                $liters = $row[3] ?? null;
                $pricePerLiter = $row[4] ?? null;
                $fuelType = trim((string) ($row[5] ?? 'Dizel'));
                $stationName = trim((string) ($row[6] ?? ''));
                $notes = trim((string) ($row[7] ?? ''));

                $vehicle = Vehicle::where('plate', $plate)->first();

                if (!$vehicle) {
                    throw new \Exception("Araç bulunamadı: {$plate}");
                }

                $station = null;
                if ($stationName !== '') {
                    $station = FuelStation::where('name', $stationName)->first();
                }

                Fuel::create([
                    'company_id' => auth()->user()->company_id,
                    'vehicle_id' => $vehicle->id,
                    'station_id' => $station?->id,
                    'station_name' => $station?->name ?? $stationName,
                    'date' => $date,
                    'km' => $km,
                    'liters' => $liters,
                    'price_per_liter' => $pricePerLiter,
                    'total_cost' => (float) $liters * (float) $pricePerLiter,
                    'fuel_type' => $fuelType ?: 'Dizel',
                    'notes' => $notes,
                ]);

                $this->importedCount++;
            } catch (\Throwable $e) {
                $this->errors[] = [
                    'row' => $index + 2,
                    'message' => $e->getMessage(),
                ];
            }
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Exports\FuelsExport;
use App\Models\Document;
use App\Models\Fuel;
use App\Models\Payroll;
use App\Models\Trip;
use App\Models\Fleet\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\TrafficPenalty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('reports.view'), 403);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $trips = Trip::with(['serviceRoute.customer', 'vehicle', 'driver'])
            ->whereBetween('trip_date', [$startDate, $endDate])
            ->orderByDesc('trip_date')
            ->get();

        $fuels = Fuel::with(['vehicle', 'station'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderByDesc('date')
            ->get();

        $documents = Document::with('documentable')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->orderByDesc('end_date')
            ->get();

        $periodMonthStart = substr($startDate, 0, 7);
        $periodMonthEnd = substr($endDate, 0, 7);

        $payrolls = Payroll::with('driver')
            ->whereBetween('period_month', [$periodMonthStart, $periodMonthEnd])
            ->orderByDesc('period_month')
            ->get();

        $maintenances = VehicleMaintenance::with('vehicle')
            ->whereBetween('service_date', [$startDate, $endDate])
            ->orderByDesc('service_date')
            ->get();

        $penalties = TrafficPenalty::with(['driver', 'vehicle'])
            ->whereBetween('penalty_date', [$startDate, $endDate])
            ->orderByDesc('penalty_date')
            ->get();

        $tripCount = $trips->count();
        $tripIncome = $trips->sum('trip_price');
        $fuelCost = $fuels->sum('total_cost');
        $salaryCost = $payrolls->sum('net_salary');
        $maintenanceCost = $maintenances->sum('amount');
        $penaltyCost = $penalties->sum('penalty_amount');
        $documentCount = $documents->count();
        $netProfit = $tripIncome - ($fuelCost + $salaryCost + $maintenanceCost + $penaltyCost);

        // 6 Months Trend Data for Chart.js
        $trendMonths = [];
        $trendIncomes = [];
        $trendExpenses = [];
        $trendProfits = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->translatedFormat('M Y');
            
            $mInc = Trip::whereBetween('trip_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])->sum('trip_price');
            $mExpF = Fuel::whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])->sum('total_cost');
            $mExpP = Payroll::where('period_month', $monthStart->format('Y-m'))->sum('net_salary');
            $mExpM = VehicleMaintenance::whereBetween('service_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])->sum('amount');
            $mExpPn = TrafficPenalty::whereBetween('penalty_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])->sum('penalty_amount');
            
            $mExpTotal = $mExpF + $mExpP + $mExpM + $mExpPn;
            
            $trendMonths[] = $monthLabel;
            $trendIncomes[] = (float)$mInc;
            $trendExpenses[] = (float)$mExpTotal;
            $trendProfits[] = (float)($mInc - $mExpTotal);
        }

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'trips',
            'fuels',
            'documents',
            'payrolls',
            'maintenances',
            'penalties',
            'tripCount',
            'tripIncome',
            'fuelCost',
            'salaryCost',
            'maintenanceCost',
            'penaltyCost',
            'documentCount',
            'netProfit',
            'trendMonths',
            'trendIncomes',
            'trendExpenses',
            'trendProfits'
        ));
    }

    public function exportTripsCsv(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->hasPermission('reports.view'), 403);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $trips = Trip::with(['serviceRoute.customer', 'vehicle', 'driver'])
            ->whereBetween('trip_date', [$startDate, $endDate])
            ->orderByDesc('trip_date')
            ->get();

        return response()->streamDownload(function () use ($trips) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Tarih',
                'Hat',
                'Müşteri',
                'Araç',
                'Şoför',
                'Durum',
                'Fiyat'
            ], ';');

            foreach ($trips as $trip) {
                fputcsv($handle, [
                    optional($trip->trip_date)->format('d.m.Y'),
                    $trip->serviceRoute?->route_name,
                    $trip->serviceRoute?->customer?->company_name,
                    $trip->vehicle?->plate,
                    $trip->driver?->full_name,
                    $trip->trip_status,
                    $trip->trip_price,
                ], ';');
            }

            fclose($handle);
        }, 'sefer-raporu.csv');
    }

    public function exportPayrollsCsv(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->hasPermission('reports.view'), 403);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $periodMonthStart = substr($startDate, 0, 7);
        $periodMonthEnd = substr($endDate, 0, 7);

        $payrolls = Payroll::with('driver')
            ->whereBetween('period_month', [$periodMonthStart, $periodMonthEnd])
            ->orderByDesc('period_month')
            ->get();

        return response()->streamDownload(function () use ($payrolls) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Ay',
                'Şoför',
                'Ana Maaş',
                'Ek Ödeme',
                'Kesinti',
                'Avans',
                'Net Maaş'
            ], ';');

            foreach ($payrolls as $payroll) {
                fputcsv($handle, [
                    $payroll->period_month,
                    $payroll->driver?->full_name,
                    $payroll->base_salary,
                    $payroll->extra_payment,
                    $payroll->deduction,
                    $payroll->advance_payment,
                    $payroll->net_salary,
                ], ';');
            }

            fclose($handle);
        }, 'maas-raporu.csv');
    }

    public function exportFuelsCsv(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('reports.view'), 403);

        $filters = $request->all();

        $vehicleId = $request->input('vehicle_id');

        /*
        |--------------------------------------------------------------------------
        | Araç detay sekmesinden gelen filtreler
        |--------------------------------------------------------------------------
        */
        $fuelStartDate = $request->input('fuel_start_date');
        $fuelEndDate = $request->input('fuel_end_date');

        /*
        |--------------------------------------------------------------------------
        | Genel rapor ekranından gelen filtreler
        |--------------------------------------------------------------------------
        */
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $effectiveStartDate = $fuelStartDate ?: $startDate;
        $effectiveEndDate = $fuelEndDate ?: $endDate;

        $fileName = 'YAKIT_KAYITLARI.xlsx';

        if ($vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
            $plate = $vehicle?->plate
                ? preg_replace('/[^A-Za-z0-9]/', '_', $vehicle->plate)
                : 'ARAC';

            if ($effectiveStartDate || $effectiveEndDate) {
                $startLabel = $effectiveStartDate
                    ? Carbon::parse($effectiveStartDate)->format('d.m.Y')
                    : 'BASLANGIC';

                $endLabel = $effectiveEndDate
                    ? Carbon::parse($effectiveEndDate)->format('d.m.Y')
                    : 'BITIS';

                $fileName = $plate . '_YAKIT_RAPORU_' . $startLabel . '_' . $endLabel . '.xlsx';
            } else {
                $fileName = $plate . '_YAKIT_KAYITLARI.xlsx';
            }
        } else {
            if ($effectiveStartDate || $effectiveEndDate) {
                $startLabel = $effectiveStartDate
                    ? Carbon::parse($effectiveStartDate)->format('d.m.Y')
                    : 'BASLANGIC';

                $endLabel = $effectiveEndDate
                    ? Carbon::parse($effectiveEndDate)->format('d.m.Y')
                    : 'BITIS';

                $fileName = 'YAKIT_RAPORU_' . $startLabel . '_' . $endLabel . '.xlsx';
            }
        }

        return Excel::download(
            new FuelsExport($filters),
            $fileName
        );
    }

    public function exportDocumentsCsv(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->hasPermission('reports.view'), 403);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $documents = Document::with('documentable')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->orderByDesc('end_date')
            ->get();

        return response()->streamDownload(function () use ($documents) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Sahibi',
                'Belge Türü',
                'Belge Adı',
                'Başlangıç',
                'Bitiş',
                'Durum'
            ], ';');

            foreach ($documents as $document) {
                $ownerText = '-';

                if ($document->documentable_type === 'App\Models\Fleet\Vehicle') {
                    $ownerText = 'Araç - ' . ($document->documentable?->plate ?? '-');
                } elseif ($document->documentable_type === 'App\Models\Fleet\Driver') {
                    $ownerText = 'Şoför - ' . ($document->documentable?->full_name ?? '-');
                }

                fputcsv($handle, [
                    $ownerText,
                    $document->document_type,
                    $document->document_name,
                    optional($document->start_date)->format('d.m.Y'),
                    optional($document->end_date)->format('d.m.Y'),
                    $document->is_active ? 'Aktif' : 'Pasif',
                ], ';');
            }

            fclose($handle);
        }, 'belge-raporu.csv');
    }
}
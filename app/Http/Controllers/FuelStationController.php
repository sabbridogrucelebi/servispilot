<?php

namespace App\Http\Controllers;

use App\Models\FuelStation;
use App\Models\FuelStationPayment;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class FuelStationController extends Controller
{
    public function index()
    {
        $stations = FuelStation::with([
                'fuels.vehicle',
                'payments' => function ($query) {
                    $query->orderByDesc('payment_date')->orderByDesc('id');
                }
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($station) {
                $totalLiters = (float) $station->fuels->sum('liters');
                $grossTotal = (float) $station->fuels->sum('gross_total_cost');
                $discountTotal = (float) $station->fuels->sum('discount_amount');
                $netTotal = (float) $station->fuels->sum('total_cost');
                $totalPaid = (float) $station->payments->sum('amount');
                $currentDebt = $netTotal - $totalPaid;

                $station->summary = (object) [
                    'total_liters' => $totalLiters,
                    'gross_total' => $grossTotal,
                    'discount_total' => $discountTotal,
                    'net_total' => $netTotal,
                    'total_paid' => $totalPaid,
                    'current_debt' => $currentDebt,
                    'payment_count' => $station->payments->count(),
                ];

                return $station;
            });

        return view('fuel-stations.index', compact('stations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        if (empty($validated['discount_type'])) {
            $validated['discount_value'] = 0;
        }

        $validated['is_active'] = true;

        $station = FuelStation::create($validated);

        ActivityLogger::log(
            module: 'fuel_station',
            action: 'created',
            subject: $station,
            title: 'Petrol istasyonu oluşturuldu',
            description: ($station->name ?? '-') . ' için cari kayıt açıldı.',
            newValues: $station->toArray()
        );

        return redirect()
            ->route('fuel-stations.index')
            ->with('success', 'Petrol istasyonu cari kaydı oluşturuldu.');
    }

    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'fuel_station_id' => 'required|exists:fuel_stations,id',
            'payment_date' => 'required|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:nakit,havale,eft,kredi_karti,cek,diger',
            'notes' => 'nullable|string',
        ]);

        $payment = FuelStationPayment::create($validated);
        $payment->load('station');

        ActivityLogger::log(
            module: 'fuel_station_payment',
            action: 'created',
            subject: $payment,
            title: 'İstasyon ödemesi girildi',
            description: ($payment->station?->name ?? '-') . ' istasyonu için ödeme kaydı oluşturuldu.',
            newValues: [
                'fuel_station_id' => $payment->fuel_station_id,
                'payment_date' => $payment->payment_date,
                'start_date' => $payment->start_date,
                'end_date' => $payment->end_date,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'notes' => $payment->notes,
            ]
        );

        return redirect()
            ->route('fuel-stations.index')
            ->with('success', 'İstasyon ödemesi başarıyla işlendi.');
    }

    public function showPayment(FuelStationPayment $payment)
    {
        return response()->json([
            'id' => $payment->id,
            'fuel_station_id' => $payment->fuel_station_id,
            'payment_date' => optional($payment->payment_date)->format('Y-m-d'),
            'start_date' => optional($payment->start_date)->format('Y-m-d'),
            'end_date' => optional($payment->end_date)->format('Y-m-d'),
            'amount' => (float) $payment->amount,
            'payment_method' => $payment->payment_method,
            'notes' => $payment->notes,
        ]);
    }

    public function updatePayment(Request $request, FuelStationPayment $payment)
    {
        $oldValues = $payment->toArray();

        $validated = $request->validate([
            'fuel_station_id' => 'required|exists:fuel_stations,id',
            'payment_date' => 'required|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:nakit,havale,eft,kredi_karti,cek,diger',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);
        $payment->refresh()->load('station');

        ActivityLogger::log(
            module: 'fuel_station_payment',
            action: 'updated',
            subject: $payment,
            title: 'İstasyon ödemesi güncellendi',
            description: ($payment->station?->name ?? '-') . ' istasyonu için ödeme kaydı güncellendi.',
            oldValues: $oldValues,
            newValues: $payment->toArray()
        );

        return redirect()
            ->route('fuel-stations.index')
            ->with('success', 'İstasyon ödemesi güncellendi.');
    }

    public function destroyPayment(FuelStationPayment $payment)
    {
        $payment->load('station');
        $oldValues = $payment->toArray();

        ActivityLogger::log(
            module: 'fuel_station_payment',
            action: 'deleted',
            subject: $payment,
            title: 'İstasyon ödemesi silindi',
            description: ($payment->station?->name ?? '-') . ' istasyonu için ödeme kaydı silindi.',
            oldValues: $oldValues
        );

        $payment->delete();

        return redirect()
            ->route('fuel-stations.index')
            ->with('success', 'Ödeme silindi, cari borç yeniden hesaplandı.');
    }

    public function storeBulkPayment(Request $request)
    {
        $validated = $request->validate([
            'payments' => 'required|array|min:1',
            'payments.*.fuel_station_id' => 'nullable|exists:fuel_stations,id',
            'payments.*.payment_date' => 'nullable|date',
            'payments.*.amount' => 'nullable|numeric|min:0.01',
            'payments.*.payment_method' => 'nullable|in:nakit,havale,eft,kredi_karti,cek,diger',
            'payments.*.notes' => 'nullable|string',
        ]);

        $createdPayments = [];

        foreach ($validated['payments'] as $paymentData) {
            $hasRequiredData =
                !empty($paymentData['fuel_station_id']) &&
                !empty($paymentData['payment_date']) &&
                !empty($paymentData['amount']) &&
                !empty($paymentData['payment_method']);

            if (!$hasRequiredData) {
                continue;
            }

            $createdPayments[] = FuelStationPayment::create([
                'fuel_station_id' => $paymentData['fuel_station_id'],
                'payment_date' => $paymentData['payment_date'],
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'],
                'notes' => $paymentData['notes'] ?? null,
                'start_date' => $paymentData['start_date'] ?? null,
                'end_date' => $paymentData['end_date'] ?? null,
            ]);
        }

        ActivityLogger::log(
            module: 'fuel_station_payment',
            action: 'bulk_paid',
            subject: null,
            title: 'Toplu istasyon ödemesi girildi',
            description: count($createdPayments) . ' adet ödeme kaydı oluşturuldu.',
            meta: [
                'count' => count($createdPayments),
                'ids' => collect($createdPayments)->pluck('id')->values()->all(),
            ]
        );

        return redirect()
            ->route('fuel-stations.index')
            ->with('success', count($createdPayments) . ' adet ödeme işlendi.');
    }

    public function statement(Request $request, FuelStation $station)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $fuels = $station->fuels()
            ->with('vehicle')
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->orderBy('date')
            ->get();

        $payments = $station->payments()
            ->when($startDate, fn ($q) => $q->whereDate('payment_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('payment_date', '<=', $endDate))
            ->orderBy('payment_date')
            ->get();

        $summary = [
            'total_liters' => (float) $fuels->sum('liters'),
            'total_fuel_cost' => (float) $fuels->sum('total_cost'),
            'total_paid' => (float) $payments->sum('amount'),
            'current_debt' => (float) $fuels->sum('total_cost') - (float) $payments->sum('amount'),
        ];

        return view('fuel-stations.statement', compact(
            'station',
            'fuels',
            'payments',
            'summary',
            'startDate',
            'endDate'
        ));
    }
}
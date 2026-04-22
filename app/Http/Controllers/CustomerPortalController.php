<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        abort_unless($user, 403);
        abort_unless($user->user_type === 'customer_portal', 403);

        $customer = Customer::query()
            ->with([
                'contracts' => function ($query) {
                    $query->orderByDesc('year')
                        ->orderByDesc('end_date')
                        ->orderByDesc('id');
                },
                'serviceRoutes' => function ($query) {
                    $query->with(['morningVehicle', 'eveningVehicle'])
                        ->orderByDesc('id');
                },
            ])
            ->find($user->customer_id);

        abort_unless($customer, 404);

        $contracts = $customer->contracts ?? collect();

        $activeContract = $contracts->first(function ($contract) {
            return (bool) ($contract->is_active ?? false);
        });

        $serviceRoutes = $customer->serviceRoutes ?? collect();

        // INVOICE TAB LOGIC
        $selectedMonth = $request->get('month', now()->month);
        $selectedYear = $request->get('year', now()->year);
        
        $monthOptions = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
            5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
            9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
        ];
        $yearOptions = range(now()->year, 2023);

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
        ];

        return view('customer-portal.dashboard', compact(
            'user',
            'customer',
            'contracts',
            'activeContract',
            'serviceRoutes',
            'selectedMonth',
            'selectedYear',
            'monthOptions',
            'yearOptions',
            'invoiceSummary'
        ));
    }

    public function trips(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_type === 'customer_portal', 403);

        $request->merge([
            'customer_id' => $user->customer_id,
            'is_customer_portal' => true
        ]);

        return app(\App\Http\Controllers\TripController::class)->index($request);
    }
}
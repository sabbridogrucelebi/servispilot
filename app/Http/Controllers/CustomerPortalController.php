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

        return view('customer-portal.dashboard', compact(
            'user',
            'customer',
            'contracts',
            'activeContract',
            'serviceRoutes'
        ));
    }
}
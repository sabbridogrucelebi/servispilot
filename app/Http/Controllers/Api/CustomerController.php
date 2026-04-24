<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->get();

        $formatted = $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'contact_person' => $customer->contact_person,
                'is_active' => $customer->is_active,
            ];
        });

        return response()->json($formatted);
    }
}

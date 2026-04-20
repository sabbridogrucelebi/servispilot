<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Fleet\Vehicle;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->get();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_type' => 'required|string|max:100',
            'company_name' => 'required|string|max:255',
            'company_title' => 'nullable|string|max:255',
            'authorized_person' => 'nullable|string|max:255',
            'authorized_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'vat_rate' => 'required|numeric',
            'withholding_rate' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Customer::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Müşteri başarıyla eklendi.');
    }

    public function show(Customer $customer)
    {
        $contracts = $customer->contracts()->get();

        $activeContract = $contracts->first(function ($contract) {
            return $contract->is_active;
        });

        $customerUsers = User::query()
            ->where('customer_id', $customer->id)
            ->where('user_type', 'customer_portal')
            ->latest()
            ->get();

        $serviceRoutes = $customer->serviceRoutes()
            ->with(['morningVehicle', 'eveningVehicle'])
            ->latest()
            ->get();

        $activeVehicles = Vehicle::query()
            ->where('is_active', true)
            ->orderBy('plate')
            ->get();

        return view('customers.show', compact(
            'customer',
            'contracts',
            'activeContract',
            'customerUsers',
            'serviceRoutes',
            'activeVehicles'
        ));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'customer_type' => 'required|string|max:100',
            'company_name' => 'required|string|max:255',
            'company_title' => 'nullable|string|max:255',
            'authorized_person' => 'nullable|string|max:255',
            'authorized_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'vat_rate' => 'required|numeric',
            'withholding_rate' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Müşteri başarıyla güncellendi.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Müşteri silindi.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerContractController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        abort_unless(auth()->user()->hasPermission('customers.edit'), 403);

        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'contract_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $file = $request->file('contract_file');

        $path = $file->store('customer-contracts', 'public');

        $customer->contracts()->create([
            'year' => $validated['year'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return redirect()
            ->route('customers.show', [
                'customer' => $customer,
                'tab' => 'contracts',
            ])
            ->with('success', 'Sözleşme başarıyla yüklendi.');
    }

    public function destroy(Customer $customer, int $contract)
    {
        abort_unless(auth()->user()->hasPermission('customers.edit'), 403);

        $contractModel = $customer->contracts()->findOrFail($contract);

        if ($contractModel->file_path && Storage::disk('public')->exists($contractModel->file_path)) {
            Storage::disk('public')->delete($contractModel->file_path);
        }

        $contractModel->delete();

        return redirect()
            ->route('customers.show', [
                'customer' => $customer,
                'tab' => 'contracts',
            ])
            ->with('success', 'Sözleşme silindi.');
    }
}
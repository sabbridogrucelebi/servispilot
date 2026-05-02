<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CustomerContract;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractApiController extends BaseApiController
{
    /**
     * Sözleşme kayıtlarını listeler
     */
    public function index(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        
        $query = CustomerContract::whereHas('customer', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $contracts = $query->orderByDesc('year')->get();

        return $this->successResponse($contracts, 'Sözleşme kayıtları başarıyla getirildi.');
    }

    /**
     * Yeni sözleşme ekler
     */
    public function store(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'customers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'year' => 'required|integer|min:2000|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'contract_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $customer = Customer::where('company_id', $this->getCompanyId())->findOrFail($validated['customer_id']);

        $path = null;
        $originalName = null;

        if ($request->hasFile('contract_file')) {
            $file = $request->file('contract_file');
            $path = $file->store('customer-contracts', 'public');
            $originalName = $file->getClientOriginalName();
        }

        $contract = $customer->contracts()->create([
            'year' => $validated['year'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'file_path' => $path,
            'original_name' => $originalName,
        ]);

        return $this->successResponse($contract, 'Sözleşme kaydı başarıyla eklendi.', 201);
    }

    /**
     * Sözleşme günceller
     */
    public function update(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $contract = CustomerContract::whereHas('customer', function ($q) {
            $q->where('company_id', $this->getCompanyId());
        })->findOrFail($id);

        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'contract_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $path = $contract->file_path;
        $originalName = $contract->original_name;

        if ($request->hasFile('contract_file')) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            $file = $request->file('contract_file');
            $path = $file->store('customer-contracts', 'public');
            $originalName = $file->getClientOriginalName();
        }

        $contract->update([
            'year' => $validated['year'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'file_path' => $path,
            'original_name' => $originalName,
        ]);

        return $this->successResponse($contract, 'Sözleşme kaydı başarıyla güncellendi.');
    }

    /**
     * Sözleşme siler
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $contract = CustomerContract::whereHas('customer', function ($q) {
            $q->where('company_id', $this->getCompanyId());
        })->findOrFail($id);

        if ($contract->file_path && Storage::disk('public')->exists($contract->file_path)) {
            Storage::disk('public')->delete($contract->file_path);
        }

        $contract->delete();

        return $this->successResponse(null, 'Sözleşme kaydı başarıyla silindi.');
    }

    public function options(Request $request)
    {
        return $this->successResponse([], 'Seçenekler getirildi');
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CustomerServiceRoute;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceRouteApiController extends BaseApiController
{
    /**
     * Güzergah kayıtlarını listeler
     */
    public function index(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        
        $query = CustomerServiceRoute::where('company_id', $companyId);

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $routes = $query->orderByDesc('id')->get();

        return $this->successResponse($routes, 'Güzergah kayıtları başarıyla getirildi.');
    }

    /**
     * Yeni güzergah ekler
     */
    public function store(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'customers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $request->validate(['customer_id' => 'required|exists:customers,id']);

        $customer = Customer::where('company_id', $this->getCompanyId())->findOrFail($request->customer_id);

        try {
            $validated = $this->validateRoute($request);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422, $e->errors());
        }

        $payload = $this->preparePayload($validated);
        $payload['company_id'] = $this->getCompanyId();
        
        $route = $customer->serviceRoutes()->create($payload);

        return $this->successResponse($route, 'Güzergah başarıyla eklendi.', 201);
    }

    /**
     * Güzergah detayını getirir
     */
    public function show(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $route = CustomerServiceRoute::where('company_id', $this->getCompanyId())->findOrFail($id);

        return $this->successResponse($route, 'Güzergah detayları getirildi.');
    }

    /**
     * Güzergah günceller
     */
    public function update(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $route = CustomerServiceRoute::where('company_id', $this->getCompanyId())->findOrFail($id);

        // Just toggle is_active if it's the only field sent (for the switch on mobile)
        if ($request->has('is_active') && count($request->all()) <= 2) {
            $route->update(['is_active' => $request->boolean('is_active')]);
            return $this->successResponse($route, 'Güzergah durumu güncellendi.');
        }

        try {
            $validated = $this->validateRoute($request);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422, $e->errors());
        }

        $payload = $this->preparePayload($validated);
        if ($request->has('is_active')) {
            $payload['is_active'] = $request->boolean('is_active');
        }

        $route->update($payload);

        return $this->successResponse($route, 'Güzergah başarıyla güncellendi.');
    }

    /**
     * Güzergah siler
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $route = CustomerServiceRoute::where('company_id', $this->getCompanyId())->findOrFail($id);
        $route->delete();

        return $this->successResponse(null, 'Güzergah başarıyla silindi.');
    }

    public function options(Request $request)
    {
        $customers = \App\Models\Customer::where('company_id', $this->getCompanyId())->get(['id', 'company_name as name']);
        $vehicles = \App\Models\Fleet\Vehicle::where('company_id', $this->getCompanyId())->get(['id', 'plate as name']);
        $drivers = \App\Models\Fleet\Driver::where('company_id', $this->getCompanyId())->get(['id', 'full_name as name']);

        return $this->successResponse([
            'customers' => $customers,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ], 'Seçenekler getirildi');
    }

    private function validateRoute(Request $request): array
    {
        $validated = $request->validate([
            'route_name' => ['required', 'string', 'max:255'],
            'service_type' => ['required', 'string', Rule::in(['both', 'morning', 'evening', 'shift'])],
            'vehicle_type' => ['required', 'string'],
            'morning_vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'evening_vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'fee_type' => ['required', 'string', Rule::in(['free', 'paid'])],
            'morning_fee' => ['nullable', 'numeric', 'min:0'],
            'evening_fee' => ['nullable', 'numeric', 'min:0'],
            'fallback_morning_fee' => ['nullable', 'numeric', 'min:0'],
            'fallback_evening_fee' => ['nullable', 'numeric', 'min:0'],
            'saturday_pricing' => ['nullable'], // App can send string or boolean
            'sunday_pricing' => ['nullable'], // App can send string or boolean
        ], [
            'route_name.required' => 'Güzergah adı zorunludur.',
            'service_type.required' => 'Servis türü seçilmelidir.',
            'vehicle_type.required' => 'Araç cinsi seçilmelidir.',
            'fee_type.required' => 'Ücret türü seçilmelidir.',
            'morning_vehicle_id.exists' => 'Seçili sabah aracı sistemde bulunamadı (silinmiş olabilir). Lütfen aracı tekrar seçin.',
            'evening_vehicle_id.exists' => 'Seçili akşam aracı sistemde bulunamadı (silinmiş olabilir). Lütfen aracı tekrar seçin.',
            'customer_id.exists' => 'Müşteri sistemde bulunamadı.',
        ]);

        // if (in_array($validated['service_type'], ['both', 'morning', 'shift'], true) && empty($validated['morning_vehicle_id'])) {
        //     throw ValidationException::withMessages([
        //         'morning_vehicle_id' => 'Bu servis türü için ilk araç seçilmelidir.',
        //     ]);
        // }

        // if (in_array($validated['service_type'], ['both', 'evening', 'shift'], true) && empty($validated['evening_vehicle_id'])) {
        //     throw ValidationException::withMessages([
        //         'evening_vehicle_id' => 'Bu servis türü için ikinci araç seçilmelidir.',
        //     ]);
        // }

        if ($validated['fee_type'] === 'paid') {
            if (in_array($validated['service_type'], ['both', 'morning', 'shift'], true) && $validated['morning_fee'] === null) {
                throw ValidationException::withMessages([
                    'morning_fee' => 'Ücretli güzergah için ilk ücret alanı zorunludur.',
                ]);
            }

            if (in_array($validated['service_type'], ['both', 'evening', 'shift'], true) && $validated['evening_fee'] === null) {
                throw ValidationException::withMessages([
                    'evening_fee' => 'Ücretli güzergah için ikinci ücret alanı zorunludur.',
                ]);
            }
        }

        return $validated;
    }

    private function preparePayload(array $validated): array
    {
        // Handle boolean vs string 'yes'/'no' for saturday/sunday pricing depending on mobile payload format
        $saturday = isset($validated['saturday_pricing']) 
            ? (in_array($validated['saturday_pricing'], ['yes', true, 1, '1'], true)) 
            : false;
            
        $sunday = isset($validated['sunday_pricing']) 
            ? (in_array($validated['sunday_pricing'], ['yes', true, 1, '1'], true)) 
            : false;

        return [
            'route_name' => $validated['route_name'],
            'service_type' => $validated['service_type'],
            'vehicle_type' => $validated['vehicle_type'],
            'morning_vehicle_id' => in_array($validated['service_type'], ['both', 'morning', 'shift'], true)
                ? $validated['morning_vehicle_id']
                : null,
            'evening_vehicle_id' => in_array($validated['service_type'], ['both', 'evening', 'shift'], true)
                ? $validated['evening_vehicle_id']
                : null,
            'fee_type' => $validated['fee_type'],
            'morning_fee' => $validated['fee_type'] === 'paid' && in_array($validated['service_type'], ['both', 'morning', 'shift'], true)
                ? $validated['morning_fee']
                : null,
            'evening_fee' => $validated['fee_type'] === 'paid' && in_array($validated['service_type'], ['both', 'evening', 'shift'], true)
                ? $validated['evening_fee']
                : null,
            'fallback_morning_fee' => in_array($validated['service_type'], ['both', 'morning', 'shift'], true)
                ? $validated['fallback_morning_fee']
                : null,
            'fallback_evening_fee' => in_array($validated['service_type'], ['both', 'evening', 'shift'], true)
                ? $validated['fallback_evening_fee']
                : null,
            'saturday_pricing' => $saturday,
            'sunday_pricing' => $sunday,
        ];
    }
}

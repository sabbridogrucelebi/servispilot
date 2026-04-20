<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerServiceRouteController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        $validated = $this->validateRoute($request);

        $customer->serviceRoutes()->create($this->preparePayload($validated));

        return redirect()
            ->route('customers.show', [
                'customer' => $customer,
                'tab' => 'services',
            ])
            ->with('success', 'Güzergah başarıyla oluşturuldu.');
    }

    public function update(Request $request, Customer $customer, int $serviceRoute)
    {
        $route = $customer->serviceRoutes()->findOrFail($serviceRoute);

        $validated = $this->validateRoute($request);

        $route->update($this->preparePayload($validated));

        return redirect()
            ->route('customers.show', [
                'customer' => $customer,
                'tab' => 'services',
            ])
            ->with('success', 'Güzergah başarıyla güncellendi.');
    }

    public function toggleStatus(Customer $customer, int $serviceRoute)
    {
        $route = $customer->serviceRoutes()->findOrFail($serviceRoute);

        $route->is_active = !$route->is_active;
        $route->save();

        return redirect()
            ->route('customers.show', [
                'customer' => $customer,
                'tab' => 'services',
            ])
            ->with('success', $route->is_active
                ? 'Güzergah aktif edildi.'
                : 'Güzergah pasif yapıldı.');
    }

    public function destroy(Customer $customer, int $serviceRoute)
    {
        $route = $customer->serviceRoutes()->findOrFail($serviceRoute);
        $route->delete();

        return redirect()
            ->route('customers.show', [
                'customer' => $customer,
                'tab' => 'services',
            ])
            ->with('success', 'Güzergah silindi.');
    }

    private function validateRoute(Request $request): array
    {
        $validated = $request->validate([
            'route_name' => ['required', 'string', 'max:255'],
            'service_type' => ['required', 'string', Rule::in(['both', 'morning', 'evening', 'shift'])],
            'vehicle_type' => [
                'required',
                'string',
                Rule::in([
                    'MİNİBÜS (16+1)',
                    'MİDİBÜS (27+1)',
                    'OTOBÜS (45+1)',
                    'TAKSİ',
                    'VİP ARAÇ',
                ]),
            ],
            'morning_vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'evening_vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'fee_type' => ['required', 'string', Rule::in(['free', 'paid'])],
            'morning_fee' => ['nullable', 'numeric', 'min:0'],
            'evening_fee' => ['nullable', 'numeric', 'min:0'],
            'fallback_morning_fee' => ['nullable', 'numeric', 'min:0'],
            'fallback_evening_fee' => ['nullable', 'numeric', 'min:0'],
            'saturday_pricing' => ['required', 'string', Rule::in(['yes', 'no'])],
            'sunday_pricing' => ['required', 'string', Rule::in(['yes', 'no'])],
        ], [
            'route_name.required' => 'Güzergah adı zorunludur.',
            'service_type.required' => 'Servis türü seçilmelidir.',
            'vehicle_type.required' => 'Araç cinsi seçilmelidir.',
            'fee_type.required' => 'Ücret türü seçilmelidir.',
        ]);

        if (in_array($validated['service_type'], ['both', 'morning', 'shift'], true) && empty($validated['morning_vehicle_id'])) {
            throw ValidationException::withMessages([
                'morning_vehicle_id' => 'Bu servis türü için ilk araç seçilmelidir.',
            ]);
        }

        if (in_array($validated['service_type'], ['both', 'evening', 'shift'], true) && empty($validated['evening_vehicle_id'])) {
            throw ValidationException::withMessages([
                'evening_vehicle_id' => 'Bu servis türü için ikinci araç seçilmelidir.',
            ]);
        }

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
            'saturday_pricing' => $validated['saturday_pricing'] === 'yes',
            'sunday_pricing' => $validated['sunday_pricing'] === 'yes',
        ];
    }
}
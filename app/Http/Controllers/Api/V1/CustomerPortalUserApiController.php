<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerPortalUserApiController extends BaseApiController
{
    /**
     * Yeni portal kullanıcısı oluşturur
     */
    public function store(Request $request, $customerId)
    {
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $customer = Customer::where('company_id', $this->getCompanyId())->find($customerId);

        if (!$customer) {
            return $this->errorResponse('Müşteri bulunamadı.', 404);
        }

        $company = $customer->company;
        if (!is_null($company->max_users) && $company->users()->count() >= $company->max_users) {
            return $this->errorResponse('Maksimum kullanıcı kotanıza (' . $company->max_users . ') ulaştınız. Lütfen paketinizi yükseltin.', 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6'],
            'is_active' => ['boolean']
        ]);

        $user = User::create([
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'], // User modelinde set mutator var, otomatik hashlenir
            'role' => 'viewer',
            'user_type' => 'customer_portal',
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return $this->successResponse($user, 'Müşteri portal kullanıcısı başarıyla oluşturuldu.', 201);
    }

    /**
     * Portal kullanıcısını günceller
     */
    public function update(Request $request, $customerId, $portalUserId)
    {
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $customer = Customer::where('company_id', $this->getCompanyId())->find($customerId);

        if (!$customer) {
            return $this->errorResponse('Müşteri bulunamadı.', 404);
        }

        $portalUser = User::where('customer_id', $customer->id)
            ->where('user_type', 'customer_portal')
            ->find($portalUserId);

        if (!$portalUser) {
            return $this->errorResponse('Portal kullanıcısı bulunamadı.', 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($portalUser->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($portalUser->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['boolean']
        ]);

        $portalUser->name = $validated['name'];
        $portalUser->username = $validated['username'];
        $portalUser->email = $validated['email'] ?? null;
        
        if ($request->has('is_active')) {
            $portalUser->is_active = $request->boolean('is_active');
        }

        if (!empty($validated['password'])) {
            $portalUser->password = $validated['password'];
        }

        $portalUser->save();

        return $this->successResponse($portalUser, 'Portal kullanıcısı başarıyla güncellendi.');
    }

    /**
     * Portal kullanıcısının aktif/pasif durumunu değiştirir
     */
    public function toggleStatus(Request $request, $customerId, $portalUserId)
    {
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $customer = Customer::where('company_id', $this->getCompanyId())->find($customerId);

        if (!$customer) {
            return $this->errorResponse('Müşteri bulunamadı.', 404);
        }

        $portalUser = User::where('customer_id', $customer->id)
            ->where('user_type', 'customer_portal')
            ->find($portalUserId);

        if (!$portalUser) {
            return $this->errorResponse('Portal kullanıcısı bulunamadı.', 404);
        }

        $portalUser->is_active = $request->has('is_active') ? $request->boolean('is_active') : !$portalUser->is_active;
        $portalUser->save();

        return $this->successResponse(
            ['is_active' => $portalUser->is_active], 
            $portalUser->is_active ? 'Portal kullanıcısı aktif edildi.' : 'Portal kullanıcısı pasif yapıldı.'
        );
    }

    /**
     * Portal kullanıcısını siler
     */
    public function destroy(Request $request, $customerId, $portalUserId)
    {
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $customer = Customer::where('company_id', $this->getCompanyId())->find($customerId);

        if (!$customer) {
            return $this->errorResponse('Müşteri bulunamadı.', 404);
        }

        $portalUser = User::where('customer_id', $customer->id)
            ->where('user_type', 'customer_portal')
            ->find($portalUserId);

        if (!$portalUser) {
            return $this->errorResponse('Portal kullanıcısı bulunamadı.', 404);
        }

        $portalUser->delete();

        return $this->successResponse(null, 'Portal kullanıcısı başarıyla silindi.');
    }
}

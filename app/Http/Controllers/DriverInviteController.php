<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Fleet\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class DriverInviteController extends Controller
{
    public function show($token)
    {
        try {
            $companyId = Crypt::decryptString($token);
            $company = Company::findOrFail($companyId);
        } catch (DecryptException $e) {
            abort(404, 'Geçersiz veya süresi dolmuş davet linki.');
        } catch (\Exception $e) {
            abort(404, 'Firma bulunamadı.');
        }

        return view('drivers.invite', compact('company', 'token'));
    }

    public function store(Request $request, $token)
    {
        try {
            $companyId = Crypt::decryptString($token);
            $company = Company::findOrFail($companyId);
        } catch (DecryptException $e) {
            abort(404, 'Geçersiz davet linki.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'tc_no' => 'nullable|string|max:11',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'birth_date' => 'nullable|date',
            'license_class' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        // Auto Title Case
        $validated['full_name'] = mb_convert_case(mb_strtolower($validated['full_name'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

        // Force defaults for skipped fields
        $validated['company_id'] = $company->id;
        $validated['approval_status'] = 'pending';
        $validated['is_active'] = false; // Cannot be active until approved
        $validated['start_date'] = now()->toDateString(); // Default to today, they don't fill this
        $validated['base_salary'] = 0; // Default 0
        $validated['src_type'] = null; // Removed

        Driver::create($validated);

        return back()->with('success', 'Bilgileriniz firmanıza kaydedilmiştir. Yönetici onayından sonra kaydınız tamamlanacaktır.');
    }
}

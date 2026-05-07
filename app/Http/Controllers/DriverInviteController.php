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

        if (!$company->is_driver_invite_active) {
            abort(403, 'Bu firma için personel kayıt alımı şu anda kapalıdır.');
        }

        $vehicles = \App\Models\Fleet\Vehicle::where('company_id', $company->id)->orderBy('plate')->get();

        return view('drivers.invite', compact('company', 'token', 'vehicles'));
    }

    public function store(Request $request, $token)
    {
        try {
            $companyId = Crypt::decryptString($token);
            $company = Company::findOrFail($companyId);
        } catch (DecryptException $e) {
            abort(404, 'Geçersiz davet linki.');
        }

        if (!$company->is_driver_invite_active) {
            abort(403, 'Bu firma için personel kayıt alımı şu anda kapalıdır.');
        }

        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'full_name' => 'required|string|max:255',
            'tc_no' => 'required|string|max:11',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'birth_date' => 'required|date',
            'license_class' => 'required|string|max:50',
            'address' => 'required|string',
        ], [
            'required' => 'Bu alan zorunludur.',
            'email' => 'Geçerli bir e-posta adresi girin.'
        ]);

        // Auto Title Case
        $validated['full_name'] = mb_convert_case(mb_strtolower($validated['full_name'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

        // Check Duplicates
        $exists = Driver::where('company_id', $company->id)
            ->where(function($q) use ($validated) {
                $q->where('tc_no', $validated['tc_no'])
                  ->orWhere('full_name', $validated['full_name']);
            })->first();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['tc_no' => 'Sistemde zaten bu T.C. kimlik numarası veya bu isimle bir kayıt bulunmaktadır.']);
        }

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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PilotCellController extends Controller
{
    public function dashboard()
    {
        $activeTrips = \App\Models\PilotCell\PcTrip::with(['route', 'driver', 'vehicle'])
            ->where('company_id', auth()->user()->company_id)
            ->where('status', 'active')
            ->get();

        $schoolCustomers = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->get();

        return view('pilotcell.dashboard', compact('activeTrips', 'schoolCustomers'));
    }

    public function school($id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($id);

        $routes = \App\Models\PilotCell\PcRoute::with('vehicle')
            ->where('customer_id', $school->id)
            ->orderBy('id', 'desc')
            ->get();

        $vehicles = \App\Models\Fleet\Vehicle::where('company_id', auth()->user()->company_id)
            ->get(['id', 'plate', 'brand', 'model']);

        return view('pilotcell.school', compact('school', 'routes', 'vehicles'));
    }

    public function storeRoute(Request $request, $id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($id);

        $validated = $request->validate([
            'service_no' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:50',
            'hostess_name' => 'nullable|string|max:255',
            'hostess_phone' => 'nullable|string|max:50',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['customer_id'] = $school->id;
        $validated['direction'] = 'morning'; // Default value as it's required by db but not in form
        $validated['is_active'] = true;

        \App\Models\PilotCell\PcRoute::create($validated);

        return back()->with('success', 'Güzergah başarıyla tanımlandı.');
    }

    public function updateRoute(Request $request, $school_id, $route_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $route = \App\Models\PilotCell\PcRoute::where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($route_id);

        $validated = $request->validate([
            'service_no' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:50',
            'hostess_name' => 'nullable|string|max:255',
            'hostess_phone' => 'nullable|string|max:50',
        ]);

        $route->update($validated);

        return back()->with('success', 'Güzergah başarıyla güncellendi.');
    }

    public function destroyRoute($school_id, $route_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $route = \App\Models\PilotCell\PcRoute::where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($route_id);

        $route->delete();

        return back()->with('success', 'Güzergah başarıyla silindi.');
    }

    public function routeDetails($school_id, $route_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $route = \App\Models\PilotCell\PcRoute::with('vehicle', 'students')
            ->where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($route_id);

        return view('pilotcell.route_details', compact('school', 'route'));
    }

    public function storeStudent(Request $request, $school_id, $route_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $route = \App\Models\PilotCell\PcRoute::where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($route_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'parent1_name' => 'nullable|string|max:255',
            'parent1_phone' => 'nullable|string|max:50',
            'parent2_name' => 'nullable|string|max:255',
            'parent2_phone' => 'nullable|string|max:50',
            'monthly_fee' => 'nullable|numeric|min:0',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['customer_id'] = $school->id;
        $validated['is_active'] = true;
        
        // Default point until map selection is implemented
        $validated['pickup_location'] = \Illuminate\Support\Facades\DB::raw("ST_GeomFromText('POINT(39.920770 32.854110)')");
        $validated['geofence_radius'] = 500;

        $student = \App\Models\PilotCell\PcStudent::create($validated);
        
        // Attach student to the route
        $student->routes()->attach($route->id);

        return back()->with('success', 'Öğrenci başarıyla eklendi.');
    }

    public function updateStudent(Request $request, $school_id, $route_id, $student_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $student = \App\Models\PilotCell\PcStudent::where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($student_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'parent1_name' => 'nullable|string|max:255',
            'parent1_phone' => 'nullable|string|max:50',
            'parent2_name' => 'nullable|string|max:255',
            'parent2_phone' => 'nullable|string|max:50',
            'monthly_fee' => 'nullable|numeric|min:0',
        ]);

        $student->update($validated);

        return back()->with('success', 'Öğrenci bilgileri güncellendi.');
    }

    public function destroyStudent($school_id, $route_id, $student_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $student = \App\Models\PilotCell\PcStudent::where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($student_id);

        // Delete the student (it will cascade detach or we just delete it since it's created per route usually)
        $student->delete();

        return back()->with('success', 'Öğrenci kaydı silindi.');
    }

    public function studentDetails($school_id, $route_id, $student_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $route = \App\Models\PilotCell\PcRoute::where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($route_id);

        $student = \App\Models\PilotCell\PcStudent::with('debts')->where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($student_id);

        return view('pilotcell.student_details', compact('school', 'route', 'student'));
    }

    public function storeDebts(Request $request, $school_id, $route_id, $student_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $student = \App\Models\PilotCell\PcStudent::where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($student_id);

        $validated = $request->validate([
            'year' => 'required|integer',
            'months' => 'required|array',
            'amounts' => 'required|array',
        ]);

        $monthNames = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran',
            7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
        ];

        foreach ($validated['months'] as $monthNumber) {
            $amount = $validated['amounts'][$monthNumber] ?? 0;
            
            if ($amount > 0) {
                \App\Models\PilotCell\PcStudentDebt::updateOrCreate(
                    [
                        'company_id' => auth()->user()->company_id,
                        'pc_student_id' => $student->id,
                        'month_number' => $monthNumber,
                        'year' => $validated['year'],
                    ],
                    [
                        'month_name' => $monthNames[(int)$monthNumber] ?? 'Bilinmeyen',
                        'amount' => $amount,
                    ]
                );
            }
        }

        return back()->with('success', 'Borçlandırma başarıyla oluşturuldu.');
    }

    public function updateDebtPayment(Request $request, $school_id, $route_id, $student_id, $debt_id)
    {
        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:0'
        ]);

        $debt = \App\Models\PilotCell\PcStudentDebt::where('company_id', auth()->user()->company_id)
            ->where('pc_student_id', $student_id)
            ->findOrFail($debt_id);

        $debt->paid_amount = $validated['paid_amount'];
        
        // Ödenen tutar borca eşit veya büyükse tam ödendi say
        if ($debt->paid_amount >= $debt->amount) {
            $debt->is_paid = true;
        } else {
            $debt->is_paid = false;
        }
        
        $debt->save();

        return back()->with('success', 'Tahsilat bilgisi güncellendi.');
    }

    public function destroyDebt($school_id, $route_id, $student_id, $debt_id)
    {
        $debt = \App\Models\PilotCell\PcStudentDebt::where('company_id', auth()->user()->company_id)
            ->where('pc_student_id', $student_id)
            ->findOrFail($debt_id);

        $debt->delete();

        return back()->with('success', 'İlgili ayın borç kaydı iptal edildi/silindi.');
    }

    public function studentUsers($school_id, $route_id, $student_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $route = \App\Models\PilotCell\PcRoute::where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($route_id);

        $student = \App\Models\PilotCell\PcStudent::with(['parent', 'parent2'])->where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($student_id);

        return view('pilotcell.student_users', compact('school', 'route', 'student'));
    }

    public function storeStudentUser(Request $request, $school_id, $route_id, $student_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $student = \App\Models\PilotCell\PcStudent::where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($student_id);

        $validated = $request->validate([
            'parent_selection' => 'required|in:1,2',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        $name = $validated['parent_selection'] == '1' ? $student->parent1_name : $student->parent2_name;
        $phone = $validated['parent_selection'] == '1' ? $student->parent1_phone : $student->parent2_phone;

        if (!$name || !$phone) {
            return back()->withErrors(['parent_selection' => 'Seçilen velinin ad, soyad ve telefon bilgileri tam olmalıdır.']);
        }

        $user = \App\Models\User::create([
            'company_id' => auth()->user()->company_id,
            'customer_id' => $school->id,
            'name' => $name,
            'username' => $validated['username'],
            'email' => $validated['username'] . '@servispilot.com', // Dummy email if needed, or nullable
            'password' => $validated['password'],
            'role' => 'parent',
            'user_type' => 'customer_portal',
            'is_active' => true,
        ]);

        if ($validated['parent_selection'] == '1') {
            $student->parent_user_id = $user->id;
        } else {
            $student->parent2_user_id = $user->id;
        }
        $student->save();

        return back()->with('success', 'Kullanıcı başarıyla oluşturuldu.');
    }

    public function updateStudentUser(Request $request, $school_id, $route_id, $student_id, $user_id)
    {
        $user = \App\Models\User::where('company_id', auth()->user()->company_id)
            ->findOrFail($user_id);

        $validated = $request->validate([
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean'
        ]);

        if ($request->filled('password')) {
            $user->password = $validated['password'];
        }
        
        if ($request->has('is_active')) {
            $user->is_active = $validated['is_active'];
        }

        $user->save();

        return back()->with('success', 'Kullanıcı başarıyla güncellendi.');
    }

    public function destroyStudentUser($school_id, $route_id, $student_id, $user_id)
    {
        $user = \App\Models\User::where('company_id', auth()->user()->company_id)
            ->findOrFail($user_id);

        $student = \App\Models\PilotCell\PcStudent::where('company_id', auth()->user()->company_id)
            ->findOrFail($student_id);

        if ($student->parent_user_id == $user->id) {
            $student->parent_user_id = null;
        } elseif ($student->parent2_user_id == $user->id) {
            $student->parent2_user_id = null;
        }
        $student->save();

        $user->delete();

        return back()->with('success', 'Kullanıcı başarıyla silindi.');
    }
    public function routeUsers($school_id, $route_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $route = \App\Models\PilotCell\PcRoute::with('users')->where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($route_id);

        return view('pilotcell.route_users', compact('school', 'route'));
    }

    public function storeRouteUser(Request $request, $school_id, $route_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $route = \App\Models\PilotCell\PcRoute::where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($route_id);

        $validated = $request->validate([
            'personnel_type' => 'required|in:driver,hostess,other',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255', // Removed unique to allow linking existing users
            'password' => 'required|string|min:6',
        ]);

        $user = \App\Models\User::firstOrCreate([
            'username' => $validated['username']
        ], [
            'company_id' => auth()->user()->company_id,
            'customer_id' => null, 
            'name' => $validated['name'],
            'email' => $validated['username'] . '@filomerkez.com', 
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => 'personnel',
            'user_type' => 'personnel',
            'is_active' => true,
        ]);

        if (strlen($user->password) < 60) {
            $user->update(['password' => \Illuminate\Support\Facades\Hash::make($validated['password'])]);
        }

        $route->users()->syncWithoutDetaching([
            $user->id => ['personnel_type' => $validated['personnel_type']]
        ]);

        return back()->with('success', 'Araç personeli kullanıcısı başarıyla oluşturuldu. Bu telefon numarası ile mobil uygulamadan "Araç Girişi" yapabilirler.');
    }

    public function updateRouteUser(Request $request, $school_id, $route_id, $user_id)
    {
        $user = \App\Models\User::where('company_id', auth()->user()->company_id)
            ->findOrFail($user_id);

        $validated = $request->validate([
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean'
        ]);

        if ($request->filled('password')) {
            $user->password = $validated['password'];
        }
        
        if ($request->has('is_active')) {
            $user->is_active = $validated['is_active'];
        }

        $user->save();

        return back()->with('success', 'Araç personeli güncellendi.');
    }

    public function destroyRouteUser($school_id, $route_id, $user_id)
    {
        $school = \App\Models\Customer::where('company_id', auth()->user()->company_id)
            ->where('customer_type', 'Okul')
            ->findOrFail($school_id);

        $route = \App\Models\PilotCell\PcRoute::where('company_id', auth()->user()->company_id)
            ->where('customer_id', $school->id)
            ->findOrFail($route_id);

        $route->users()->detach($user_id);

        return back()->with('success', 'Araç personeli kullanıcısı güzergahtan çıkarıldı.');
    }
}

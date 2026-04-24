<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Sistem logları — sadece firma admininin göreceği özel sayfa.
     *
     * Güvenlik katmanları:
     *  1) Route middleware  : permission:logs.view
     *  2) Controller        : isCompanyAdmin() zorunlu
     *  3) Global scope      : company_id otomatik filtre (BelongsToCompany)
     */
    public function index(Request $request)
    {
        // 1. Kullanıcı firma admini değilse erişim yasak
        abort_unless(auth()->user()->isCompanyAdmin(), 403, 'Yalnızca firma yöneticileri sistem loglarını görüntüleyebilir.');

        // 2. Ek yetki kontrolü
        if (!auth()->user()->hasPermission('logs.view')) {
            abort(403, 'Sistem loglarını görüntüleme yetkiniz yok.');
        }

        // 3. Girdi temizleme (XSS / SQL-injection'a karşı whitelist)
        $validated = $request->validate([
            'module'   => ['nullable', 'string', 'max:50'],
            'action'   => ['nullable', 'string', 'in:created,updated,deleted,exported,image_uploaded,image_deleted,document_uploaded,document_deleted'],
            'user_id'  => ['nullable', 'integer', 'exists:users,id'],
            'from'     => ['nullable', 'date'],
            'to'       => ['nullable', 'date', 'after_or_equal:from'],
            'search'   => ['nullable', 'string', 'max:120'],
        ]);

        $logs = ActivityLog::query()
            ->with('user')
            ->when($validated['module'] ?? null, fn ($q, $v) => $q->where('module', $v))
            ->when($validated['action'] ?? null, fn ($q, $v) => $q->where('action', $v))
            ->when($validated['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($validated['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($validated['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($validated['search'] ?? null, function ($q, $v) {
                $needle = '%' . str_replace(['%', '_'], ['\%', '\_'], $v) . '%';
                $q->where(function ($qq) use ($needle) {
                    $qq->where('title', 'like', $needle)
                       ->orWhere('description', 'like', $needle)
                       ->orWhere('ip_address', 'like', $needle);
                });
            })
            ->latest()
            ->paginate(40)
            ->withQueryString();

        $modules = [
            'vehicles'     => 'Araçlar',
            'drivers'      => 'Personeller',
            'trips'        => 'Seferler',
            'fuels'        => 'Yakıtlar',
            'documents'    => 'Belgeler',
            'customers'    => 'Müşteriler',
            'maintenances' => 'Bakımlar',
            'penalties'    => 'Cezalar',
            'users'        => 'Kullanıcılar',
        ];

        // Sadece kendi firmasındaki kullanıcılar — BelongsToCompany ile
        // güvence altına alınsa da ek where şartıyla çifte kilit
        $users = User::where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get();

        return view('activity-logs.index', compact('logs', 'modules', 'users'));
    }
}

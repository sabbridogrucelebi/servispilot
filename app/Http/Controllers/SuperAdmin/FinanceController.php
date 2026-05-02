<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['company', 'subscription.plan'])->latest()->get();
        return view('super-admin.finance.index', compact('invoices'));
    }

    public function approve(Invoice $invoice)
    {
        if ($invoice->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'Bu fatura onay bekleyen durumda değil.');
        }

        // Ödemeyi onayla
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Aboneliği aktif et veya uzat
        $company = $invoice->company;
        $plan = $invoice->plan;

        if ($plan) {
            // Mevcut aktif aboneliği kapat
            $company->subscriptions()->where('status', 'active')->update(['status' => 'canceled', 'canceled_at' => now()]);

            // Yeni abonelik oluştur
            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(), // Aylık varsayılan
            ]);

            // Faturayı abonelikle ilişkilendir
            $invoice->update(['subscription_id' => $subscription->id]);

            // Şirketin lisans tipini ve kotalarını güncelle
            $company->update([
                'license_type' => $plan->name,
                'max_vehicles' => $plan->max_vehicles,
                'max_users' => $plan->max_users,
                'status' => \App\Enums\CompanyStatus::Active,
            ]);
        }

        return redirect()->back()->with('success', 'Ödeme onaylandı ve abonelik aktif edildi.');
    }

    public function reject(Request $request, Invoice $invoice)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($invoice->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'Bu fatura onay bekleyen durumda değil.');
        }

        $invoice->update([
            'status' => 'rejected',
            'admin_notes' => 'Reddedildi: ' . $request->reason,
        ]);

        return redirect()->back()->with('success', 'Ödeme reddedildi.');
    }
}

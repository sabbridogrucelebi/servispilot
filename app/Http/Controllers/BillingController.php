<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    public function index()
    {
        $company = auth()->user()->company;
        $activeSubscription = $company->activeSubscription();
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $invoices = $company->invoices()->latest()->get();

        return view('billing.index', compact('company', 'activeSubscription', 'plans', 'invoices'));
    }

    public function selectPlan(Plan $plan)
    {
        // Havale/EFT ile paket seçimi talebi oluşturma
        // Önce bir fatura (invoice) oluşturulur, kullanıcı dekont yükleyince admin onaylar
        
        $company = auth()->user()->company;
        
        // Zaten bekleyen bir ödeme var mı?
        $pendingInvoice = $company->invoices()->where('status', 'pending_approval')->first();
        if ($pendingInvoice) {
            return redirect()->back()->with('error', 'Zaten onay bekleyen bir ödemeniz bulunmaktadır.');
        }

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'invoice_no' => 'INV-' . date('Y') . '-' . strtoupper(Str::random(6)),
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'status' => 'unpaid',
            'due_date' => now()->addDays(3),
            'admin_notes' => null,
        ]);

        return redirect()->route('billing.invoice', $invoice)->with('success', 'Paket seçildi. Lütfen ödemeyi yapıp dekont yükleyin.');
    }

    public function showInvoice(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);
        return view('billing.invoice', compact('invoice'));
    }

    public function uploadReceipt(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $invoice->update([
                'payment_receipt_path' => $path,
                'status' => 'pending_approval'
            ]);
        }

        return redirect()->route('billing.index')->with('success', 'Dekont başarıyla yüklendi. Kontrol edildikten sonra aboneliğiniz aktif edilecektir.');
    }

    private function authorizeInvoice(Invoice $invoice)
    {
        if ($invoice->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}

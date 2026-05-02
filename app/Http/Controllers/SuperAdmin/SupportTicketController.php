<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with(['company', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->paginate(20)->withQueryString();

        return view('super-admin.support-tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load(['messages.user', 'company', 'user']);
        
        return view('super-admin.support-tickets.show', compact('supportTicket'));
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'message' => 'required|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            'status' => 'required|in:open,answered,closed',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('ticket-files', 'public');
        }

        $supportTicket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'file_path' => $filePath,
            'is_super_admin' => true,
        ]);
        
        $supportTicket->update(['status' => $request->status]);

        return back()->with('success', 'Yanıt gönderildi ve bilet durumu güncellendi.');
    }
}

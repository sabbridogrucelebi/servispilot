<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::where('company_id', auth()->user()->company_id)
            ->latest()
            ->paginate(15);
            
        return view('support-tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('support-tickets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'priority' => 'required|in:low,normal,high,urgent',
            'message' => 'required|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $ticket = SupportTicket::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'priority' => $request->priority,
            'status' => 'open',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('ticket-files', 'public');
        }

        $ticket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'file_path' => $filePath,
            'is_super_admin' => false,
        ]);

        return redirect()->route('support-tickets.show', $ticket)->with('success', 'Destek talebiniz oluşturuldu.');
    }

    public function show(SupportTicket $supportTicket)
    {
        abort_if($supportTicket->company_id !== auth()->user()->company_id, 403);
        
        $supportTicket->load(['messages.user', 'user']);
        
        return view('support-tickets.show', compact('supportTicket'));
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        abort_if($supportTicket->company_id !== auth()->user()->company_id, 403);

        $request->validate([
            'message' => 'required|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('ticket-files', 'public');
        }

        $supportTicket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'file_path' => $filePath,
            'is_super_admin' => false,
        ]);
        
        $supportTicket->update(['status' => 'open']); // Yeniden açıldı veya adminin görmesi için statü değişebilir

        return back()->with('success', 'Mesajınız gönderildi.');
    }
}

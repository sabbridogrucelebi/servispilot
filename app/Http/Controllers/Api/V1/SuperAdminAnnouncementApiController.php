<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GlobalAnnouncement;
use Illuminate\Http\Request;

class SuperAdminAnnouncementApiController extends Controller
{
    public function index()
    {
        return response()->json(GlobalAnnouncement::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'nullable|string|in:info,warning,danger,success',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $announcement = GlobalAnnouncement::create($validated);
        return response()->json($announcement, 201);
    }

    public function show($id)
    {
        return response()->json(GlobalAnnouncement::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $announcement = GlobalAnnouncement::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'type' => 'nullable|string|in:info,warning,danger,success',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $announcement->update($validated);
        return response()->json($announcement);
    }

    public function destroy($id)
    {
        $announcement = GlobalAnnouncement::findOrFail($id);
        $announcement->delete();
        return response()->json(null, 204);
    }
    
    // For clients (Tenants / Mobile)
    public function active()
    {
        $now = now();
        $announcements = GlobalAnnouncement::where('is_active', true)
            ->where(function($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($announcements);
    }
}

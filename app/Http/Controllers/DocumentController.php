<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Fleet\Driver;
use App\Models\Fleet\Vehicle;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::latest()->get();

        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        $vehicles = Vehicle::orderBy('plate')->get();
        $drivers = Driver::orderBy('full_name')->get();

        return view('documents.create', compact('vehicles', 'drivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_type' => 'required|in:vehicle,driver',
            'owner_id' => 'required|integer',
            'document_type' => 'required|string|max:100',
            'document_name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'notes' => 'nullable|string',
        ]);

        if ($validated['owner_type'] === 'vehicle') {
            $documentableType = Vehicle::class;
        } else {
            $documentableType = Driver::class;
        }

        $filePath = null;

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('documents', 'public');
        }

        Document::create([
            'documentable_type' => $documentableType,
            'documentable_id' => $validated['owner_id'],
            'document_type' => $validated['document_type'],
            'document_name' => $validated['document_name'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'file_path' => $filePath,
            'is_active' => $request->has('is_active'),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('documents.index')->with('success', 'Belge başarıyla eklendi.');
    }

    public function edit(Document $document)
    {
        $vehicles = Vehicle::orderBy('plate')->get();
        $drivers = Driver::orderBy('full_name')->get();

        $ownerType = $document->documentable_type === Vehicle::class ? 'vehicle' : 'driver';

        return view('documents.edit', compact('document', 'vehicles', 'drivers', 'ownerType'));
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'owner_type' => 'required|in:vehicle,driver',
            'owner_id' => 'required|integer',
            'document_type' => 'required|string|max:100',
            'document_name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'notes' => 'nullable|string',
        ]);

        if ($validated['owner_type'] === 'vehicle') {
            $documentableType = Vehicle::class;
        } else {
            $documentableType = Driver::class;
        }

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('documents', 'public');
            $document->file_path = $filePath;
        }

        $document->update([
            'documentable_type' => $documentableType,
            'documentable_id' => $validated['owner_id'],
            'document_type' => $validated['document_type'],
            'document_name' => $validated['document_name'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => $request->has('is_active'),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('documents.index')->with('success', 'Belge güncellendi.');
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Belge silindi.');
    }
}
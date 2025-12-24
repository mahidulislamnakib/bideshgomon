<?php

namespace App\Http\Controllers\Api\UserProfile;

use App\Http\Controllers\Controller;
use App\Models\UserDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserDocumentController extends Controller
{
    /**
     * Store a newly uploaded document
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'document_number' => 'nullable|string|max:100',
            'issuing_authority' => 'nullable|string|max:255',
        ]);

        try {
            $file = $request->file('file');

            // Store file
            $path = $file->store('documents/'.Auth::id(), 'public');

            // Create document record
            $document = UserDocument::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'document_type' => $validated['document_type'],
                'description' => $validated['description'] ?? null,
                // Original migration columns (required)
                'original_filename' => $file->getClientOriginalName(),
                'storage_path' => $path,
                // New columns
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'size_bytes' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'issue_date' => $validated['issue_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'document_number' => $validated['document_number'] ?? null,
                'issuing_authority' => $validated['issuing_authority'] ?? null,
                'is_verified' => false,
                'is_shared' => false,
                'status' => 'pending',
            ]);

            Log::info('Document uploaded successfully', [
                'user_id' => Auth::id(),
                'document_id' => $document->id,
                'type' => $document->document_type,
            ]);

            return back()->with('success', 'Document uploaded successfully!');

        } catch (\Exception $e) {
            Log::error('Document upload failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['file' => 'Failed to upload document. Please try again.']);
        }
    }

    /**
     * Download a document
     */
    public function download($id)
    {
        $document = UserDocument::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $filePath = $document->file_path;

        if (! Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download(
            Storage::disk('public')->path($filePath),
            $document->file_name
        );
    }

    /**
     * Delete a document
     */
    public function destroy($id)
    {
        $document = UserDocument::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            // Delete file from storage
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Delete record
            $document->delete();

            Log::info('Document deleted successfully', [
                'user_id' => Auth::id(),
                'document_id' => $id,
            ]);

            return back()->with('success', 'Document deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Document deletion failed', [
                'user_id' => Auth::id(),
                'document_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to delete document. Please try again.']);
        }
    }
}

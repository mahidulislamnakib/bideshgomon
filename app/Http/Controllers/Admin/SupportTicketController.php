<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of support tickets with filters
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedTo', 'replies'])
            ->withCount('replies')
            ->latest();

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Priority filter
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        // Category filter
        if ($request->category) {
            $query->where('category', $request->category);
        }

        // Search by ticket number or subject
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('ticket_number', 'like', "%{$request->search}%")
                    ->orWhere('subject', 'like', "%{$request->search}%");
            });
        }

        // Assigned filter
        if ($request->assigned === 'me') {
            $query->where('assigned_to', auth()->id());
        } elseif ($request->assigned === 'unassigned') {
            $query->whereNull('assigned_to');
        }

        $tickets = $query->paginate(20);

        // Get staff members for assignment dropdown
        $staffMembers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'consultant']);
        })->select('id', 'name', 'email')->get();

        return Inertia::render('Admin/Support/Index', [
            'tickets' => $tickets,
            'staffMembers' => $staffMembers,
            'filters' => $request->only(['status', 'priority', 'category', 'search', 'assigned']),
            'stats' => [
                'open' => SupportTicket::where('status', 'open')->count(),
                'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
                'resolved' => SupportTicket::where('status', 'resolved')->count(),
                'urgent' => SupportTicket::where('priority', 'urgent')->where('status', '!=', 'closed')->count(),
            ],
        ]);
    }

    /**
     * Display the specified ticket with full conversation
     */
    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load(['user', 'assignedTo', 'replies.user']);

        // Get staff members for assignment dropdown
        $staffMembers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'consultant']);
        })->select('id', 'name', 'email')->get();

        return Inertia::render('Admin/Support/Show', [
            'ticket' => $supportTicket,
            'staffMembers' => $staffMembers,
        ]);
    }

    /**
     * Assign ticket to a staff member
     */
    public function assign(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $supportTicket->update([
            'assigned_to' => $request->assigned_to,
            'status' => $supportTicket->status === 'open' ? 'in_progress' : $supportTicket->status,
        ]);

        return back()->with('success', __('Ticket assigned successfully.'));
    }

    /**
     * Close a ticket
     */
    public function close(SupportTicket $supportTicket)
    {
        $supportTicket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return back()->with('success', __('Ticket closed successfully.'));
    }

    /**
     * Reopen a closed ticket
     */
    public function reopen(SupportTicket $supportTicket)
    {
        $supportTicket->update([
            'status' => 'open',
            'closed_at' => null,
        ]);

        return back()->with('success', __('Ticket reopened successfully.'));
    }

    /**
     * Reply to a ticket (staff response)
     */
    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'internal_note' => 'nullable|boolean',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('support-tickets', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
        }

        SupportTicketReply::create([
            'support_ticket_id' => $supportTicket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_staff_reply' => true,
            'is_internal_note' => $request->boolean('internal_note', false),
            'attachments' => $attachments,
        ]);

        // Update ticket status and timestamp
        $supportTicket->update([
            'status' => $supportTicket->status === 'open' ? 'in_progress' : $supportTicket->status,
            'replied_at' => now(),
        ]);

        return back()->with('success', __('Reply added successfully.'));
    }

    /**
     * Update ticket priority
     */
    public function updatePriority(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        $supportTicket->update([
            'priority' => $request->priority,
        ]);

        return back()->with('success', __('Priority updated successfully.'));
    }

    /**
     * Update ticket status
     */
    public function update(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,awaiting_reply,resolved,closed',
        ]);

        $updateData = ['status' => $request->status];

        if ($request->status === 'resolved') {
            $updateData['resolved_at'] = now();
        } elseif ($request->status === 'closed') {
            $updateData['closed_at'] = now();
        }

        $supportTicket->update($updateData);

        return back()->with('success', __('Ticket status updated successfully.'));
    }

    /**
     * Delete a ticket (soft delete)
     */
    public function destroy(SupportTicket $supportTicket)
    {
        $supportTicket->delete();

        return redirect()->route('admin.support-tickets.index')
            ->with('success', __('Ticket deleted successfully.'));
    }
}

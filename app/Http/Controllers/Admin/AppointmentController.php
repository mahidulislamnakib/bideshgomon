<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    /**
     * Display a listing of all appointments.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['user', 'assignedTo'])
            ->latest('appointment_date');

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('appointment_type', $request->type);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhere('appointment_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        if ($request->date_from) {
            $query->where('appointment_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('appointment_date', '<=', $request->date_to);
        }

        $appointments = $query->paginate(20)->withQueryString();

        // Get stats
        $stats = [
            'total' => Appointment::count(),
            'pending' => Appointment::pending()->count(),
            'confirmed' => Appointment::confirmed()->count(),
            'completed' => Appointment::completed()->count(),
            'cancelled' => Appointment::cancelled()->count(),
            'upcoming' => Appointment::upcoming()->count(),
        ];

        // Get staff members for assignment
        $staff = User::whereIn('role', ['admin', 'consultant'])
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Appointments/Index', [
            'appointments' => $appointments,
            'stats' => $stats,
            'staff' => $staff,
            'filters' => $request->only(['status', 'type', 'search', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['user', 'assignedTo']);

        // Get staff members for assignment
        $staff = User::whereIn('role', ['admin', 'consultant'])
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Appointments/Show', [
            'appointment' => $appointment,
            'staff' => $staff,
        ]);
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'appointment_date' => 'sometimes|date',
            'appointment_time' => 'sometimes|date_format:H:i',
            'status' => 'sometimes|in:pending,confirmed,completed,cancelled',
            'meeting_link' => 'nullable|url',
            'assigned_to' => 'nullable|exists:users,id',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update($validated);

        return back()->with('success', 'Appointment updated successfully.');
    }

    /**
     * Confirm an appointment.
     */
    public function confirm(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'meeting_link' => 'nullable|url',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'meeting_link' => $validated['meeting_link'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? $appointment->admin_notes,
        ]);

        return back()->with('success', 'Appointment confirmed successfully.');
    }

    /**
     * Mark an appointment as completed.
     */
    public function complete(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update([
            'status' => 'completed',
            'completed_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? $appointment->admin_notes,
        ]);

        return back()->with('success', 'Appointment marked as completed.');
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return back()->with('success', 'Appointment cancelled.');
    }

    /**
     * Assign an appointment to a staff member.
     */
    public function assign(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $appointment->update([
            'assigned_to' => $validated['assigned_to'],
        ]);

        return back()->with('success', 'Staff member assigned successfully.');
    }

    /**
     * Display calendar view.
     */
    public function calendar(Request $request)
    {
        $appointments = Appointment::with(['user', 'assignedTo'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('appointment_date', '>=', now()->subMonth())
            ->where('appointment_date', '<=', now()->addMonths(3))
            ->get()
            ->map(function ($apt) {
                $userName = $apt->user?->name ?? 'Unknown';
                $purpose = ucfirst(str_replace('_', ' ', $apt->purpose ?? 'appointment'));
                $date = $apt->appointment_date ? $apt->appointment_date->format('Y-m-d') : now()->format('Y-m-d');
                $time = $apt->appointment_time ?? '09:00';

                return [
                    'id' => $apt->id,
                    'title' => $userName.' - '.$purpose,
                    'start' => $date.'T'.$time,
                    'status' => $apt->status,
                    'type' => $apt->appointment_type,
                    'url' => route('admin.appointments.show', $apt->id),
                ];
            });

        return Inertia::render('Admin/Appointments/Calendar', [
            'appointments' => $appointments,
        ]);
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }
}

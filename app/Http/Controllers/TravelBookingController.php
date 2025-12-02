<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class TravelBookingController extends Controller
{
    /**
     * Show the booking calendar
     */
    public function create(Request $request)
    {
        // Get consultants (users with consultant role)
        $consultants = User::role('consultant')
            ->select('id', 'name', 'email')
            ->get();

        // If no consultants, use admin users as fallback
        if ($consultants->isEmpty()) {
            $consultants = User::role('admin')
                ->select('id', 'name', 'email')
                ->take(3)
                ->get();
        }

        // Get available slots for next 30 days
        $availableSlots = $this->getAvailableSlots($request->consultant_id);

        return Inertia::render('Services/Travel/Book', [
            'consultants' => $consultants,
            'availableSlots' => $availableSlots
        ]);
    }

    /**
     * Store a new booking
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'consultant_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'appointment_type' => 'required|in:online_meeting,office_visit',
            'purpose' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if slot is still available
        $existingAppointment = Appointment::where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->where('assigned_to', $validated['consultant_id'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($existingAppointment) {
            return back()->withErrors(['appointment_time' => 'This time slot is no longer available. Please select another time.']);
        }

        // Check business hours (9 AM - 6 PM)
        $time = Carbon::createFromFormat('H:i', $validated['appointment_time']);
        if ($time->hour < 9 || $time->hour >= 18) {
            return back()->withErrors(['appointment_time' => 'Appointments are only available between 9:00 AM and 6:00 PM.']);
        }

        // Create appointment
        $appointment = Appointment::create([
            'user_id' => auth()->id(),
            'assigned_to' => $validated['consultant_id'],
            'appointment_type' => $validated['appointment_type'],
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'purpose' => $validated['purpose'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'is_online' => $validated['appointment_type'] === 'online_meeting',
            'duration' => '60', // 1 hour consultation
        ]);

        // TODO: Send email notification to user and consultant

        return redirect()->route('travel.bookings.confirmation', $appointment->id)
            ->with('success', 'Your travel consultation has been booked successfully!');
    }

    /**
     * Show booking confirmation
     */
    public function confirmation($id)
    {
        $appointment = Appointment::with(['user', 'assignedTo'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Services/Travel/Confirmation', [
            'appointment' => $appointment
        ]);
    }

    /**
     * Get available time slots for next 30 days
     */
    private function getAvailableSlots($consultantId = null)
    {
        $slots = [];
        $now = Carbon::now();
        $endDate = $now->copy()->addDays(30);

        // Business hours: 9 AM - 6 PM
        $businessHours = [
            '09:00', '10:00', '11:00', '12:00', 
            '13:00', '14:00', '15:00', '16:00', '17:00'
        ];

        for ($date = $now->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            $dateStr = $date->toDateString();
            $slots[$dateStr] = [];

            foreach ($businessHours as $time) {
                // Skip past time slots for today
                if ($date->isToday()) {
                    $slotTime = Carbon::createFromFormat('Y-m-d H:i', $dateStr . ' ' . $time);
                    if ($slotTime->isPast()) {
                        continue;
                    }
                }

                // Check if slot is available
                $query = Appointment::where('appointment_date', $dateStr)
                    ->where('appointment_time', $time)
                    ->whereIn('status', ['pending', 'confirmed']);

                if ($consultantId) {
                    $query->where('assigned_to', $consultantId);
                }

                $isBooked = $query->exists();

                if (!$isBooked) {
                    $slots[$dateStr][] = $time;
                }
            }
        }

        return $slots;
    }

    /**
     * Show user's bookings
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['assignedTo'])
            ->where('user_id', auth()->id())
            ->latest('appointment_date');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(15);

        return Inertia::render('Services/Travel/MyBookings', [
            'bookings' => $bookings,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Show booking details
     */
    public function show($id)
    {
        $booking = Appointment::with(['assignedTo', 'user'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Services/Travel/ShowBooking', [
            'booking' => $booking
        ]);
    }

    /**
     * Cancel booking
     */
    public function cancel($id, Request $request)
    {
        $appointment = Appointment::where('id', $id)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'confirmed'])
            ->firstOrFail();

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason ?? 'User requested cancellation'
        ]);

        return redirect()->route('travel.bookings.index')
            ->with('success', 'Booking cancelled successfully.');
    }
}

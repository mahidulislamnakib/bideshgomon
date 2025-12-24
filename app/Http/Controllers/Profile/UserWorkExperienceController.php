<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\UserWorkExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserWorkExperienceController extends Controller
{
    /**
     * Display a listing of the user's work experience records.
     */
    public function index()
    {
        $experiences = Auth::user()->workExperiences()->orderBy('start_date', 'desc')->get();

        return response()->json($experiences);
    }

    /**
     * Store a newly created work experience record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'employment_type' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current_employment' => 'boolean',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'job_description' => 'nullable|string|max:2000',
            'salary' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'salary_period' => 'nullable|string|in:monthly,yearly',
            'supervisor_name' => 'nullable|string|max:255',
            'supervisor_phone' => 'nullable|string|max:20',
            'supervisor_email' => 'nullable|email|max:255',
            'reason_for_leaving' => 'nullable|string|max:1000',
            'employment_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $validated['user_id'] = Auth::id();

        // Handle file uploads
        if ($request->hasFile('employment_letter')) {
            $path = $request->file('employment_letter')->store('work-experience/letters', 'public');
            $validated['employment_letter_path'] = $path;
        }

        $experience = UserWorkExperience::create($validated);

        return response()->json($experience, 201);
    }

    /**
     * Update the specified work experience record.
     */
    public function update(Request $request, UserWorkExperience $userWorkExperience)
    {
        // Ensure the user owns this work experience record
        if ($userWorkExperience->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'employment_type' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current_employment' => 'boolean',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'job_description' => 'nullable|string|max:2000',
            'salary' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'salary_period' => 'nullable|string|in:monthly,yearly',
            'supervisor_name' => 'nullable|string|max:255',
            'supervisor_phone' => 'nullable|string|max:20',
            'supervisor_email' => 'nullable|email|max:255',
            'reason_for_leaving' => 'nullable|string|max:1000',
            'employment_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Handle file uploads
        if ($request->hasFile('employment_letter')) {
            if ($userWorkExperience->employment_letter_path) {
                Storage::disk('public')->delete($userWorkExperience->employment_letter_path);
            }
            $path = $request->file('employment_letter')->store('work-experience/letters', 'public');
            $validated['employment_letter_path'] = $path;
        }

        $userWorkExperience->update($validated);

        return response()->json($userWorkExperience);
    }

    /**
     * Remove the specified work experience record.
     */
    public function destroy(UserWorkExperience $userWorkExperience)
    {
        // Ensure the user owns this work experience record
        if ($userWorkExperience->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Delete associated files
        if ($userWorkExperience->employment_letter_path) {
            Storage::disk('public')->delete($userWorkExperience->employment_letter_path);
        }

        $userWorkExperience->delete();

        return response()->json(['message' => 'Work experience record deleted successfully']);
    }
}

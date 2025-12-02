<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\University;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['university.country'])
            ->active();

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('university_id')) {
            $query->byUniversity($request->university_id);
        }

        if ($request->filled('subject')) {
            $query->bySubject($request->subject);
        }

        if ($request->filled('level')) {
            $query->byLevel($request->level);
        }

        if ($request->filled('study_mode')) {
            $query->byStudyMode($request->study_mode);
        }

        if ($request->filled('tuition_max')) {
            $query->byTuitionRange($request->tuition_max);
        }

        if ($request->filled('scholarships') && $request->scholarships) {
            $query->where('scholarships_available', true);
        }

        if ($request->filled('duration_max')) {
            $query->where('duration_months', '<=', $request->duration_max);
        }

        // Sort
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $courses = $query->paginate(24);

        // Get filter options
        $universities = University::active()
            ->orderBy('name')
            ->get(['id', 'name', 'country_id']);

        $subjects = Course::active()
            ->distinct()
            ->pluck('subject')
            ->sort()
            ->values();

        $levels = Course::active()
            ->distinct()
            ->pluck('level')
            ->sort()
            ->values();

        $studyModes = Course::active()
            ->distinct()
            ->pluck('study_mode')
            ->sort()
            ->values();

        // Stats
        $stats = [
            'total' => Course::active()->count(),
            'universities' => Course::active()->distinct('university_id')->count('university_id'),
            'with_scholarships' => Course::active()->where('scholarships_available', true)->count(),
            'subjects' => Course::active()->distinct('subject')->count('subject'),
        ];

        return Inertia::render('Services/Courses/Index', [
            'courses' => $courses,
            'universities' => $universities,
            'subjects' => $subjects,
            'levels' => $levels,
            'studyModes' => $studyModes,
            'stats' => $stats,
            'filters' => $request->only(['search', 'university_id', 'subject', 'level', 'study_mode', 'tuition_max', 'scholarships', 'duration_max']),
        ]);
    }

    public function show(Course $course)
    {
        $course->load(['university.country']);

        // Get related courses (same university, same subject)
        $relatedCourses = Course::with(['university'])
            ->active()
            ->where('id', '!=', $course->id)
            ->where(function ($query) use ($course) {
                $query->where('university_id', $course->university_id)
                      ->orWhere('subject', $course->subject);
            })
            ->limit(6)
            ->get();

        return Inertia::render('Services/Courses/Show', [
            'course' => $course,
            'relatedCourses' => $relatedCourses,
        ]);
    }
}


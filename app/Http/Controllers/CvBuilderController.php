<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CvTemplate;
use App\Models\UserCv;
use App\Services\CvBuilderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CvBuilderController extends Controller
{
    protected CvBuilderService $cvBuilderService;

    public function __construct(CvBuilderService $cvBuilderService)
    {
        $this->cvBuilderService = $cvBuilderService;
    }

    /**
     * Display CV templates listing
     */
    public function index()
    {
        $templates = CvTemplate::active()
            ->ordered()
            ->get()
            ->groupBy('category');

        $userCvs = UserCv::forUser(auth()->id())
            ->with('cvTemplate')
            ->latest()
            ->get();

        return Inertia::render('Services/CvBuilder/Index', [
            'templates' => $templates,
            'userCvs' => $userCvs,
        ]);
    }

    /**
     * Show CV template details (redirect to create with template)
     */
    public function showTemplate($slug)
    {
        $template = CvTemplate::where('slug', $slug)->active()->firstOrFail();

        return redirect()->route('cv-builder.create', ['template' => $template->id]);
    }

    /**
     * Show CV builder form
     */
    public function create(Request $request)
    {
        $templateId = $request->query('template');
        $template = CvTemplate::findOrFail($templateId);

        $user = auth()->user();

        // Get profile data from service
        $profileData = $this->cvBuilderService->getUserProfileData($user);

        $countries = Country::orderBy('name')->get();

        return Inertia::render('Services/CvBuilder/Create', [
            'template' => $template,
            'user' => $user,
            'countries' => $countries,
            ...$profileData,  // Spread the profile data array
        ]);
    }

    /**
     * Store new CV
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cv_template_id' => 'required|exists:cv_templates,id',
            'title' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'city' => 'nullable|string|max:100',
            'country_id' => 'nullable|exists:countries,id',
            'address' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'professional_summary' => 'required|string|min:50',
            'education' => 'required|array|min:1',
            'experience' => 'required|array|min:1',
            'skills' => 'required|array|min:1',
            'languages' => 'nullable|array',
            'certifications' => 'nullable|array',
        ]);

        try {
            $cv = $this->cvBuilderService->createCv($validated, auth()->user());

            return redirect()->route('cv-builder.my-cvs')
                ->with('success', 'CV created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors([
                'payment' => $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Show user's CVs
     */
    public function myCvs()
    {
        $cvs = UserCv::forUser(auth()->id())
            ->with(['cvTemplate', 'country'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Services/CvBuilder/MyCvs', [
            'cvs' => $cvs,
        ]);
    }

    /**
     * Show edit CV form
     */
    public function edit($id)
    {
        $cv = UserCv::where('id', $id)
            ->forUser(auth()->id())
            ->with(['cvTemplate', 'country'])
            ->firstOrFail();

        $countries = Country::orderBy('name')->get();

        return Inertia::render('Services/CvBuilder/Edit', [
            'cv' => $cv,
            'countries' => $countries,
        ]);
    }

    /**
     * Update CV
     */
    public function update(Request $request, $id)
    {
        $cv = UserCv::where('id', $id)
            ->forUser(auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'city' => 'nullable|string|max:255',
            'country_id' => 'nullable|exists:countries,id',
            'address' => 'nullable|string|max:500',
            'linkedin_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'professional_summary' => 'nullable|string',
            'education' => 'nullable|array',
            'experience' => 'nullable|array',
            'skills' => 'nullable|array',
            'languages' => 'nullable|array',
            'certifications' => 'nullable|array',
            'projects' => 'nullable|array',
            'references' => 'nullable|array',
        ]);

        $this->cvBuilderService->updateCv($cv, $validated);

        return redirect()->back()->with('success', 'CV updated successfully!');
    }

    /**
     * Delete CV
     */
    public function destroy($id)
    {
        $cv = UserCv::where('id', $id)
            ->forUser(auth()->id())
            ->firstOrFail();

        $this->cvBuilderService->deleteCv($cv);

        return redirect()->route('cv-builder.my-cvs')
            ->with('success', 'CV deleted successfully!');
    }

    /**
     * Preview CV
     */
    public function preview($id)
    {
        $cv = UserCv::where('id', $id)
            ->forUser(auth()->id())
            ->with(['cvTemplate', 'country'])
            ->firstOrFail();

        $cv->incrementViewCount();

        return Inertia::render('Services/CvBuilder/Preview', [
            'cv' => $cv,
        ]);
    }

    /**
     * Download CV as PDF
     */
    public function download($id)
    {
        $cv = UserCv::with(['cvTemplate', 'country'])
            ->forUser(auth()->user()->id)
            ->findOrFail($id);

        return $this->cvBuilderService->downloadCvPdf($cv);
    }

    /**
     * Duplicate an existing CV
     */
    public function duplicate($id)
    {
        $cv = UserCv::where('id', $id)
            ->forUser(auth()->id())
            ->firstOrFail();

        $newCv = $this->cvBuilderService->duplicateCv($cv, auth()->user());

        return redirect()->route('cv-builder.edit', $newCv->id)
            ->with('success', 'CV duplicated successfully! You can now edit it.');
    }

    /**
     * Toggle CV sharing (public/private)
     */
    public function toggleShare($id)
    {
        $cv = UserCv::where('id', $id)
            ->forUser(auth()->id())
            ->firstOrFail();

        if ($cv->is_public) {
            $this->cvBuilderService->makePrivate($cv);
            $message = 'CV is now private';
        } else {
            $this->cvBuilderService->makePublic($cv);
            $message = 'CV is now public and shareable';
        }

        return back()->with('success', $message);
    }

    /**
     * Generate AI-powered professional summary
     */
    public function generateAiSummary(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'experience' => 'nullable|array',
            'skills' => 'nullable|array',
            'education' => 'nullable|array',
        ]);

        $summary = $this->cvBuilderService->generateProfessionalSummary(
            $validated['full_name'],
            $validated['experience'] ?? [],
            $validated['skills'] ?? [],
            $validated['education'] ?? []
        );

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }
}

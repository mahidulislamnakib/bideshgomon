<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HelpController extends Controller
{
    /**
     * Display the Help Center landing page.
     */
    public function index()
    {
        // Define category slugs we expect in the system
        $categoryMapping = [
            'visa' => ['name' => 'Visa & Immigration', 'color' => 'blue'],
            'jobs' => ['name' => 'Jobs & Applications', 'color' => 'green'],
            'account' => ['name' => 'Account & Profile', 'color' => 'purple'],
            'payment' => ['name' => 'Payment & Wallet', 'color' => 'orange'],
            'services' => ['name' => 'Services & Booking', 'color' => 'indigo'],
            'technical' => ['name' => 'Technical Support', 'color' => 'red'],
        ];

        // Get existing categories from database
        $dbCategories = FaqCategory::with(['activeFaqs' => function ($query) {
            $query->select('id', 'faq_category_id');
        }])
            ->get()
            ->keyBy('slug');

        // Build categories array with counts
        $categories = [];
        foreach ($categoryMapping as $slug => $info) {
            $dbCategory = $dbCategories->get($slug);
            $categories[] = [
                'slug' => $slug,
                'name' => $info['name'],
                'color' => $info['color'],
                'count' => $dbCategory ? $dbCategory->activeFaqs->count() : 0,
            ];
        }

        return Inertia::render('Help/Index', [
            'categories' => $categories,
            'totalFaqs' => Faq::published()->count(),
            'totalTickets' => auth()->check() ? SupportTicket::where('user_id', auth()->id())->count() : 0,
        ]);
    }

    /**
     * Display FAQs for a specific category.
     */
    public function category(Request $request, string $categorySlug)
    {
        $validCategories = ['visa', 'jobs', 'account', 'payment', 'services', 'technical'];

        if (! in_array($categorySlug, $validCategories)) {
            abort(404);
        }

        // Find the category by slug
        $category = FaqCategory::where('slug', $categorySlug)->first();

        if (! $category) {
            // Return empty results if category doesn't exist yet
            $faqs = collect([]);
        } else {
            $faqs = Faq::where('faq_category_id', $category->id)
                ->published()
                ->ordered()
                ->paginate(20);
        }

        $categoryNames = [
            'visa' => 'Visa & Immigration',
            'jobs' => 'Jobs & Applications',
            'account' => 'Account & Profile',
            'payment' => 'Payment & Wallet',
            'services' => 'Services & Booking',
            'technical' => 'Technical Support',
        ];

        return Inertia::render('Help/Category', [
            'category' => $categorySlug,
            'categoryName' => $categoryNames[$categorySlug] ?? ucfirst($categorySlug),
            'faqs' => $faqs,
        ]);
    }

    /**
     * Search help content (FAQs).
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return redirect()->route('help.index');
        }

        $faqs = Faq::published()
            ->where(function ($q) use ($query) {
                $q->where('question', 'like', "%{$query}%")
                    ->orWhere('answer', 'like', "%{$query}%");
            })
            ->with('category')
            ->orderByRaw('
                CASE 
                    WHEN question LIKE ? THEN 1
                    WHEN question LIKE ? THEN 2
                    ELSE 3
                END
            ', ["{$query}%", "%{$query}%"])
            ->paginate(20)
            ->appends(['q' => $query]);

        return Inertia::render('Help/Search', [
            'query' => $query,
            'faqs' => $faqs,
            'resultsCount' => $faqs->total(),
        ]);
    }
}

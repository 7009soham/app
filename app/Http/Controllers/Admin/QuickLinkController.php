<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use App\Models\QuickLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuickLinkController extends Controller
{
    public function index()
    {
        $quickLinks = QuickLink::with('customPage')->ordered()->get();

        return view('admin.quick-links.index', compact('quickLinks'));
    }

    public function create()
    {
        return view('admin.quick-links.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateLink($request);

        DB::transaction(function () use ($request, $validated) {
            $link = QuickLink::create($this->attributesFor($request, $validated));

            if ($this->writesItsOwnPage($request)) {
                $page = $this->syncPage($request, null, $validated['title']);

                $link->update([
                    'custom_page_id' => $page->id,
                    'url' => '/page/' . $page->slug,
                ]);
            }
        });

        return redirect()->route('admin.quick-links.index')
            ->with('success', 'Quick link created successfully.');
    }

    public function edit(QuickLink $quickLink)
    {
        $quickLink->load('customPage');

        return view('admin.quick-links.edit', compact('quickLink'));
    }

    public function update(Request $request, QuickLink $quickLink)
    {
        $validated = $this->validateLink($request);

        DB::transaction(function () use ($request, $validated, $quickLink) {
            $attributes = $this->attributesFor($request, $validated);

            if ($this->writesItsOwnPage($request)) {
                $page = $this->syncPage($request, $quickLink->customPage, $validated['title']);

                $attributes['custom_page_id'] = $page->id;
                $attributes['url'] = '/page/' . $page->slug;
            } else {
                // Switched back to a plain URL. The page is kept rather than
                // deleted so its content is not lost by an accidental toggle;
                // it stays editable under Pages.
                $attributes['custom_page_id'] = null;
            }

            $quickLink->update($attributes);
        });

        return redirect()->route('admin.quick-links.index')
            ->with('success', 'Quick link updated successfully.');
    }

    public function destroy(QuickLink $quickLink)
    {
        // Only the link is removed. Any page it created remains under Pages,
        // since other links or printed material may reference that URL.
        $quickLink->delete();

        return redirect()->route('admin.quick-links.index')
            ->with('success', 'Quick link deleted successfully.');
    }

    private function writesItsOwnPage(Request $request): bool
    {
        return $request->input('link_type') === 'page';
    }

    private function validateLink(Request $request): array
    {
        $writesPage = $this->writesItsOwnPage($request);

        return $request->validate([
            'title' => 'required|string|max:255',
            // The URL is derived from the page when content is written here.
            'url' => [$writesPage ? 'nullable' : 'required', 'string', 'max:255'],
            'page_content' => [$writesPage ? 'required' : 'nullable', 'string', 'max:200000'],
            'icon' => 'nullable|string|max:100',
            'location' => 'required|in:header,footer',
            'order' => 'nullable|integer|min:0',
        ], [
            'page_content.required' => 'Add some page content, or switch to linking an existing URL.',
        ]);
    }

    private function attributesFor(Request $request, array $validated): array
    {
        return [
            'title' => $validated['title'],
            'url' => $validated['url'] ?? '#',
            'icon' => $validated['icon'] ?? null,
            'location' => $validated['location'],
            'order' => (int) ($validated['order'] ?? 0),
            'is_active' => $request->has('is_active'),
            'open_new_tab' => $request->has('open_new_tab'),
        ];
    }

    /**
     * Create or update the page backing this link. Content is sanitised by the
     * CustomPage mutator, so nothing extra is needed here.
     */
    private function syncPage(Request $request, ?CustomPage $existing, string $title): CustomPage
    {
        $content = (string) $request->input('page_content');

        if ($existing) {
            $existing->update([
                'title' => $title,
                'content' => $content,
                'is_published' => true,
            ]);

            return $existing;
        }

        return CustomPage::create([
            'title' => $title,
            'slug' => CustomPage::uniqueSlug($title),
            'content' => $content,
            'is_published' => true,
        ]);
    }
}

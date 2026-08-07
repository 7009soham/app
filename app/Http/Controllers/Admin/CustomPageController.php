<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use Illuminate\Http\Request;

class CustomPageController extends Controller
{
    public function index()
    {
        $pages = CustomPage::ordered()->get();

        return view('admin.custom-pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.custom-pages.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePage($request);

        $validated['slug'] = CustomPage::uniqueSlug(
            $validated['slug'] !== '' ? $validated['slug'] : $validated['title']
        );
        $validated['is_published'] = $request->has('is_published');
        $validated['order'] = (int) ($request->input('order') ?? 0);

        $page = CustomPage::create($validated);

        Logger::log("Created page \"{$page->title}\"", $page, 'content');

        return redirect()->route('admin.custom-pages.index')
            ->with('success', "Page \"{$page->title}\" created. Public URL: {$page->url}");
    }

    public function edit(CustomPage $customPage)
    {
        return view('admin.custom-pages.edit', ['page' => $customPage]);
    }

    public function update(Request $request, CustomPage $customPage)
    {
        $validated = $this->validatePage($request);

        $validated['slug'] = CustomPage::uniqueSlug(
            $validated['slug'] !== '' ? $validated['slug'] : $validated['title'],
            $customPage->id
        );
        $validated['is_published'] = $request->has('is_published');
        $validated['order'] = (int) ($request->input('order') ?? 0);

        $customPage->update($validated);

        Logger::log("Updated page \"{$customPage->title}\"", $customPage, 'content');

        return redirect()->route('admin.custom-pages.index')
            ->with('success', "Page \"{$customPage->title}\" updated.");
    }

    public function destroy(CustomPage $customPage)
    {
        $title = $customPage->title;
        $slug = $customPage->slug;

        $customPage->delete();

        Logger::log("Deleted page \"{$title}\"", null, 'content');

        return redirect()->route('admin.custom-pages.index')
            ->with('success', "Page \"{$title}\" deleted. Remove any quick link pointing at /page/{$slug}.");
    }

    private function validatePage(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            // Slug may be left blank; it is then derived from the title.
            'slug' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:100',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string|max:200000',
            'order' => 'nullable|integer|min:0|max:9999',
        ]);

        $validated['slug'] = trim((string) ($validated['slug'] ?? ''));

        return $validated;
    }
}

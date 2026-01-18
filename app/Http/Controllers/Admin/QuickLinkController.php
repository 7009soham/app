<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuickLink;
use Illuminate\Http\Request;

class QuickLinkController extends Controller
{
    public function index()
    {
        $quickLinks = QuickLink::ordered()->get();
        return view('admin.quick-links.index', compact('quickLinks'));
    }

    public function create()
    {
        return view('admin.quick-links.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'location' => 'required|in:header,footer',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
            'open_new_tab' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['open_new_tab'] = $request->has('open_new_tab');
        $validated['order'] = $request->order ?? 0;

        QuickLink::create($validated);

        return redirect()->route('admin.quick-links.index')->with('success', 'Quick link created successfully.');
    }

    public function edit(QuickLink $quickLink)
    {
        return view('admin.quick-links.edit', compact('quickLink'));
    }

    public function update(Request $request, QuickLink $quickLink)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'location' => 'required|in:header,footer',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
            'open_new_tab' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['open_new_tab'] = $request->has('open_new_tab');
        $validated['order'] = $request->order ?? 0;

        $quickLink->update($validated);

        return redirect()->route('admin.quick-links.index')->with('success', 'Quick link updated successfully.');
    }

    public function destroy(QuickLink $quickLink)
    {
        $quickLink->delete();

        return redirect()->route('admin.quick-links.index')->with('success', 'Quick link deleted successfully.');
    }

    public function show(QuickLink $quickLink)
    {
        return redirect()->route('admin.quick-links.edit', $quickLink);
    }
}

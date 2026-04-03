<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenaltySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenaltySettingController extends Controller
{
    /**
     * Check if user has permission
     */
    private function authorize()
    {
        $user = Auth::guard('admin')->user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('penalty.manage')) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Display a listing of penalty settings
     */
    public function index()
    {
        $this->authorize();
        
        $settings = PenaltySetting::orderBy('tax_type')->orderBy('created_at', 'desc')->get();
        
        return view('admin.penalty-settings.index', compact('settings'));
    }

    /**
     * Show the form for creating a new penalty setting
     */
    public function create()
    {
        $this->authorize();
        
        return view('admin.penalty-settings.create');
    }

    /**
     * Store a newly created penalty setting
     */
    public function store(Request $request)
    {
        $this->authorize();
        
        $validated = $request->validate([
            'tax_type' => 'required|in:water_tax,property_tax',
            'name' => 'required|string|max:255',
            'grace_days' => 'required|integer|min:0|max:365',
            'penalty_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // If this is set to active, deactivate other settings for the same tax type
        if ($validated['is_active']) {
            PenaltySetting::where('tax_type', $validated['tax_type'])->update(['is_active' => false]);
        }

        PenaltySetting::create($validated);

        \App\Helpers\Logger::log("Created penalty setting: {$validated['name']}", null, 'settings');

        return redirect()->route('admin.penalty-settings.index')
            ->with('success', 'Penalty setting created successfully.');
    }

    /**
     * Show the form for editing the specified penalty setting
     */
    public function edit(PenaltySetting $penaltySetting)
    {
        $this->authorize();
        
        return view('admin.penalty-settings.edit', compact('penaltySetting'));
    }

    /**
     * Update the specified penalty setting
     */
    public function update(Request $request, PenaltySetting $penaltySetting)
    {
        $this->authorize();
        
        $validated = $request->validate([
            'tax_type' => 'required|in:water_tax,property_tax',
            'name' => 'required|string|max:255',
            'grace_days' => 'required|integer|min:0|max:365',
            'penalty_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        // If this is set to active, deactivate other settings for the same tax type
        if ($validated['is_active']) {
            PenaltySetting::where('tax_type', $validated['tax_type'])
                ->where('id', '!=', $penaltySetting->id)
                ->update(['is_active' => false]);
        }

        $penaltySetting->update($validated);

        \App\Helpers\Logger::log("Updated penalty setting: {$validated['name']}", $penaltySetting, 'settings');

        return redirect()->route('admin.penalty-settings.index')
            ->with('success', 'Penalty setting updated successfully.');
    }

    /**
     * Remove the specified penalty setting
     */
    public function destroy(PenaltySetting $penaltySetting)
    {
        $this->authorize();
        
        $name = $penaltySetting->name;
        $penaltySetting->delete();

        \App\Helpers\Logger::log("Deleted penalty setting: {$name}", null, 'settings');

        return redirect()->route('admin.penalty-settings.index')
            ->with('success', 'Penalty setting deleted successfully.');
    }
}

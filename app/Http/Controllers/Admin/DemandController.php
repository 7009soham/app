<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DemandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $demands = \App\Models\Demand::withCount(['citizens', 'waterTaxRecords', 'propertyTaxRecords', 'propertyAssessments'])->paginate(20);
        return view('admin.demands.index', compact('demands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.demands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:demands,name',
            'description' => 'nullable|string',
        ]);

        \App\Models\Demand::create($validated);

        return redirect()->route('admin.demands.index')
                         ->with('success', 'Demand created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // View demand details
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $demand = \App\Models\Demand::findOrFail($id);
        return view('admin.demands.edit', compact('demand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $demand = \App\Models\Demand::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:demands,name,' . $demand->id,
            'description' => 'nullable|string',
        ]);

        $demand->update($validated);

        return redirect()->route('admin.demands.index')
                         ->with('success', 'Demand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $demand = \App\Models\Demand::findOrFail($id);

        if ($demand->citizens()->count() > 0 || $demand->waterTaxRecords()->count() > 0 || $demand->propertyTaxRecords()->count() > 0 || $demand->propertyAssessments()->count() > 0) {
            return redirect()->route('admin.demands.index')
                             ->with('error', 'Cannot delete demand because it has related records.');
        }

        $demand->delete();

        return redirect()->route('admin.demands.index')
                         ->with('success', 'Demand deleted successfully.');
    }
}

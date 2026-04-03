<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Grievance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GrievanceController extends Controller
{
    /**
     * Get the authenticated citizen
     */
    private function getCitizen()
    {
        return Auth::guard('citizen')->user();
    }

    /**
     * Display a listing of the citizen's grievances
     */
    public function index()
    {
        $citizen = $this->getCitizen();

        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        $grievances = Grievance::where('citizen_id', $citizen->id)
            ->orWhere('phone', $citizen->phone)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('citizen.grievances.index', compact('citizen', 'grievances'));
    }

    /**
     * Show the form for creating a new grievance
     */
    public function create()
    {
        $citizen = $this->getCitizen();

        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        $categories = Grievance::getCategories();
        return view('citizen.grievances.create', compact('citizen', 'categories'));
    }

    /**
     * Store a newly created grievance
     */
    public function store(Request $request)
    {
        $citizen = $this->getCitizen();

        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        $validated = $request->validate([
            'category' => 'required|string|in:' . implode(',', array_keys(Grievance::getCategories())),
            'description' => 'required|string|min:20|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_address' => 'nullable|string|max:500',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('grievances', 'public');
        }

        // Auto-fill citizen information
        $validated['citizen_id'] = $citizen->id;
        $validated['name'] = $citizen->name;
        $validated['phone'] = $citizen->phone;
        $validated['email'] = $citizen->email ?? null;
        $validated['address'] = $citizen->address ?? null;

        // Create grievance
        $grievance = Grievance::create($validated);

        return redirect()->route('citizen.grievances.show', $grievance->id)
            ->with('success', 'Your grievance has been submitted successfully. Ticket No: ' . $grievance->ticket_no);
    }

    /**
     * Display the specified grievance
     */
    public function show($id)
    {
        $citizen = $this->getCitizen();

        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        $grievance = Grievance::where('id', $id)
            ->where(function ($query) use ($citizen) {
                $query->where('citizen_id', $citizen->id)
                    ->orWhere('phone', $citizen->phone);
            })
            ->firstOrFail();

        return view('citizen.grievances.show', compact('citizen', 'grievance'));
    }
}

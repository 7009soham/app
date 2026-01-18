<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GrievanceController extends Controller
{
    /**
     * Show the grievance submission form
     */
    public function create()
    {
        $categories = Grievance::getCategories();
        return view('grievance.create', compact('categories'));
    }

    /**
     * Store a new grievance
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|size:10',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
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

        // Create grievance
        $grievance = Grievance::create($validated);

        return redirect()->route('grievance.success', ['ticket' => $grievance->ticket_no])
            ->with('success', 'Your grievance has been submitted successfully.');
    }

    /**
     * Show success page with ticket number
     */
    public function success(Request $request)
    {
        $ticketNo = $request->get('ticket');
        $grievance = Grievance::where('ticket_no', $ticketNo)->first();

        if (!$grievance) {
            return redirect()->route('grievance.create')
                ->with('error', 'Grievance not found.');
        }

        return view('grievance.success', compact('grievance'));
    }

    /**
     * Track grievance status
     */
    public function track()
    {
        return view('grievance.track');
    }

    /**
     * Show grievance status
     */
    public function status(Request $request)
    {
        $request->validate([
            'ticket_no' => 'required|string',
        ]);

        // Clean and uppercase the ticket number
        $ticketNo = strtoupper(trim($request->ticket_no));

        $grievance = Grievance::where('ticket_no', $ticketNo)->first();

        if (!$grievance) {
            return back()->with('error', 'No grievance found with this ticket number.');
        }

        return view('grievance.status', compact('grievance'));
    }
}

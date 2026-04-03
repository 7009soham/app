<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GrievanceController extends Controller
{
    /**
     * Show the grievance submission form
     */
    public function create()
    {
        // Login is required for grievance submission
        if (!Auth::guard('citizen')->check()) {
            return redirect()->route('citizen.login')
                ->with('error', 'Please login to submit a grievance.');
        }

        return redirect()->route('citizen.grievances.create');
    }

    /**
     * Store a new grievance
     */
    public function store(Request $request)
    {
        // Login is required for grievance submission
        if (!Auth::guard('citizen')->check()) {
            return redirect()->route('citizen.login')
                ->with('error', 'Please login to submit a grievance.');
        }

        // Redirect to citizen grievance store
        return redirect()->route('citizen.grievances.create');
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

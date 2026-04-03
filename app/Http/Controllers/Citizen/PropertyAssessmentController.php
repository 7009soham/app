<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\PropertyAssessment;
use App\Models\PropertyTaxRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyAssessmentController extends Controller
{
    /**
     * Get the authenticated citizen.
     */
    private function getCitizen()
    {
        return Auth::guard('citizen')->user();
    }

    /**
     * Show property assessment list for the citizen.
     */
    public function index()
    {
        $citizen = $this->getCitizen();

        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        // Get property tax records linked to this citizen
        $propertyTaxRecords = PropertyTaxRecord::where('citizen_id', $citizen->id)
            ->orWhere('phone', $citizen->phone)
            ->orderBy('a_no')
            ->get();

        // Get property numbers from the citizen's tax records
        $propertyNumbers = $propertyTaxRecords->pluck('property_no')->filter()->unique()->toArray();

        // Get assessments matching the citizen's properties
        $assessments = collect();
        if (!empty($propertyNumbers)) {
            $assessments = PropertyAssessment::whereIn('property_number', $propertyNumbers)
                ->orderBy('financial_year', 'desc')
                ->orderBy('sr_no')
                ->get()
                ->unique('group_id');
        }

        return view('citizen.property-assessment.index', compact(
            'citizen',
            'assessments',
            'propertyTaxRecords'
        ));
    }

    /**
     * Show assessment print (with watermark).
     */
    public function printAssessment($id)
    {
        $citizen = $this->getCitizen();

        if (!$citizen) {
            return redirect()->route('citizen.login');
        }

        $assessment = PropertyAssessment::findOrFail($id);

        // Verify the citizen owns this assessment
        $propertyTaxRecords = PropertyTaxRecord::where('citizen_id', $citizen->id)
            ->orWhere('phone', $citizen->phone)
            ->get();

        $propertyNumbers = $propertyTaxRecords->pluck('property_no')->filter()->unique()->toArray();

        if (!in_array($assessment->property_number, $propertyNumbers)) {
            return redirect()->route('citizen.property-assessment.index')
                ->with('error', 'You do not have access to this assessment.');
        }

        $rows = $assessment->getExtensionRows();

        return view('citizen.property-assessment.print', compact('assessment', 'rows', 'citizen'));
    }
}

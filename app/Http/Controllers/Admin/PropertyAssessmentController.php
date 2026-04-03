<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PropertyAssessmentController extends Controller
{
    /**
     * List assessments with search & filter.
     */
    public function index(Request $request)
    {
        $query = PropertyAssessment::query();

        // For extended type, show only parent rows in the listing
        $query->where(function ($q) {
            $q->whereNull('group_id')
              ->orWhereIn('id', function ($sub) {
                  $sub->selectRaw('MIN(id)')
                      ->from('property_assessments')
                      ->whereNotNull('group_id')
                      ->groupBy('group_id');
              });
        });

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('property_number', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%")
                  ->orWhere('tenant_name', 'like', "%{$search}%")
                  ->orWhere('sr_no', '=', $search);
            });
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('assessment_type', $type);
        }

        // Filter by financial year
        if ($fy = $request->input('fy')) {
            $query->where('financial_year', $fy);
        }

        $assessments = $query->orderBy('sr_no')->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total'    => PropertyAssessment::whereNull('group_id')
                            ->orWhereIn('id', function ($sub) {
                                $sub->selectRaw('MIN(id)')->from('property_assessments')
                                    ->whereNotNull('group_id')->groupBy('group_id');
                            })->count(),
            'regular'  => PropertyAssessment::where('assessment_type', 'regular')->count(),
            'rented'   => PropertyAssessment::where('assessment_type', 'rented')->count(),
            'extended' => PropertyAssessment::where('assessment_type', 'extended')
                            ->whereIn('id', function ($sub) {
                                $sub->selectRaw('MIN(id)')->from('property_assessments')
                                    ->whereNotNull('group_id')->groupBy('group_id');
                            })->count(),
        ];

        // Available financial years
        $financialYears = PropertyAssessment::select('financial_year')
            ->distinct()->orderBy('financial_year', 'desc')->pluck('financial_year');

        return view('admin.property-assessments.index', compact('assessments', 'stats', 'financialYears'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.property-assessments.create');
    }

    /**
     * Store a new assessment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'assessment_type'    => 'required|in:regular,rented,extended',
            'financial_year'     => 'required|string|max:10',

            // Arrays for multiple rows (extended)
            'sr_no'              => 'required|array|min:1',
            'sr_no.*'            => 'required|integer',
            'property_number'    => 'required|array',
            'property_number.*'  => 'required|string|max:255',
            'description'        => 'nullable|array',
            'description.*'      => 'nullable|string|max:255',
            'owner_name'         => 'required|array',
            'owner_name.*'       => 'required|string|max:255',
            'tenant_name'        => 'nullable|array',
            'tenant_name.*'      => 'nullable|string|max:255',

            'year_of_construction'   => 'nullable|array',
            'year_of_construction.*' => 'nullable|integer',
            'length'             => 'nullable|array',
            'length.*'           => 'nullable|numeric',
            'width'              => 'nullable|array',
            'width.*'            => 'nullable|numeric',
            'square_foot'        => 'nullable|array',
            'square_foot.*'      => 'nullable|numeric',
            'square_meter'       => 'nullable|array',
            'square_meter.*'     => 'nullable|numeric',

            'rr_rate_land'       => 'nullable|array',
            'rr_rate_land.*'     => 'nullable|numeric',
            'rr_rate_building'   => 'nullable|array',
            'rr_rate_building.*' => 'nullable|numeric',
            'amount_with_depreciation'   => 'nullable|array',
            'amount_with_depreciation.*' => 'nullable|numeric',
            'total'              => 'nullable|array',
            'total.*'            => 'nullable|numeric',
            'rate_of_education'  => 'nullable|array',
            'rate_of_education.*'=> 'nullable|numeric',
            'rate_of_bearable'   => 'nullable|array',
            'rate_of_bearable.*' => 'nullable|numeric',
            'capital_value'      => 'nullable|array',
            'capital_value.*'    => 'nullable|numeric',
            'tax_rate'           => 'nullable|array',
            'tax_rate.*'         => 'nullable|numeric',
            'house_tax'          => 'nullable|array',
            'house_tax.*'        => 'nullable|numeric',
            'light_tax'          => 'nullable|array',
            'light_tax.*'        => 'nullable|numeric',
            'health_tax'         => 'nullable|array',
            'health_tax.*'       => 'nullable|numeric',
            'grand_total'        => 'nullable|array',
            'grand_total.*'      => 'nullable|numeric',
        ]);

        $type = $validated['assessment_type'];
        $fy   = $validated['financial_year'];
        $rowCount = count($validated['sr_no']);

        // Generate group_id for extended type with multiple rows
        $groupId = ($type === 'extended' && $rowCount > 1)
            ? PropertyAssessment::generateGroupId()
            : null;

        for ($i = 0; $i < $rowCount; $i++) {
            PropertyAssessment::create([
                'assessment_type'          => $type,
                'group_id'                 => $groupId,
                'sr_no'                    => $validated['sr_no'][$i],
                'property_number'          => $validated['property_number'][$i],
                'description'              => $validated['description'][$i] ?? null,
                'owner_name'               => $validated['owner_name'][$i],
                'tenant_name'              => ($type === 'rented') ? ($validated['tenant_name'][$i] ?? null) : null,
                'year_of_construction'     => $validated['year_of_construction'][$i] ?? null,
                'length'                   => $validated['length'][$i] ?? null,
                'width'                    => $validated['width'][$i] ?? null,
                'square_foot'              => $validated['square_foot'][$i] ?? null,
                'square_meter'             => $validated['square_meter'][$i] ?? null,
                'rr_rate_land'             => $validated['rr_rate_land'][$i] ?? 0,
                'rr_rate_building'         => $validated['rr_rate_building'][$i] ?? 0,
                'amount_with_depreciation' => $validated['amount_with_depreciation'][$i] ?? 0,
                'total'                    => $validated['total'][$i] ?? 0,
                'rate_of_education'        => $validated['rate_of_education'][$i] ?? 0,
                'rate_of_bearable'         => $validated['rate_of_bearable'][$i] ?? 0,
                'capital_value'            => $validated['capital_value'][$i] ?? 0,
                'tax_rate'                 => $validated['tax_rate'][$i] ?? 0,
                'house_tax'                => $validated['house_tax'][$i] ?? 0,
                'light_tax'                => $validated['light_tax'][$i] ?? 0,
                'health_tax'               => $validated['health_tax'][$i] ?? 0,
                'grand_total'              => $validated['grand_total'][$i] ?? 0,
                'financial_year'           => $fy,
            ]);
        }

        return redirect()->route('admin.property-assessments.index')
            ->with('success', 'Assessment created successfully.');
    }

    /**
     * Show a single assessment (or group for extended).
     */
    public function show($id)
    {
        $assessment = PropertyAssessment::findOrFail($id);
        $rows = $assessment->getExtensionRows();

        return view('admin.property-assessments.show', compact('assessment', 'rows'));
    }

    /**
     * Edit form.
     */
    public function edit($id)
    {
        $assessment = PropertyAssessment::findOrFail($id);
        $rows = $assessment->getExtensionRows();

        return view('admin.property-assessments.edit', compact('assessment', 'rows'));
    }

    /**
     * Update an assessment.
     */
    public function update(Request $request, $id)
    {
        $assessment = PropertyAssessment::findOrFail($id);

        $validated = $request->validate([
            'assessment_type'    => 'required|in:regular,rented,extended',
            'financial_year'     => 'required|string|max:10',
            'sr_no'              => 'required|array|min:1',
            'sr_no.*'            => 'required|integer',
            'property_number'    => 'required|array',
            'property_number.*'  => 'required|string|max:255',
            'description'        => 'nullable|array',
            'description.*'      => 'nullable|string|max:255',
            'owner_name'         => 'required|array',
            'owner_name.*'       => 'required|string|max:255',
            'tenant_name'        => 'nullable|array',
            'tenant_name.*'      => 'nullable|string|max:255',
            'year_of_construction'   => 'nullable|array',
            'year_of_construction.*' => 'nullable|integer',
            'length'             => 'nullable|array',
            'length.*'           => 'nullable|numeric',
            'width'              => 'nullable|array',
            'width.*'            => 'nullable|numeric',
            'square_foot'        => 'nullable|array',
            'square_foot.*'      => 'nullable|numeric',
            'square_meter'       => 'nullable|array',
            'square_meter.*'     => 'nullable|numeric',
            'rr_rate_land'       => 'nullable|array',
            'rr_rate_land.*'     => 'nullable|numeric',
            'rr_rate_building'   => 'nullable|array',
            'rr_rate_building.*' => 'nullable|numeric',
            'amount_with_depreciation'   => 'nullable|array',
            'amount_with_depreciation.*' => 'nullable|numeric',
            'total'              => 'nullable|array',
            'total.*'            => 'nullable|numeric',
            'rate_of_education'  => 'nullable|array',
            'rate_of_education.*'=> 'nullable|numeric',
            'rate_of_bearable'   => 'nullable|array',
            'rate_of_bearable.*' => 'nullable|numeric',
            'capital_value'      => 'nullable|array',
            'capital_value.*'    => 'nullable|numeric',
            'tax_rate'           => 'nullable|array',
            'tax_rate.*'         => 'nullable|numeric',
            'house_tax'          => 'nullable|array',
            'house_tax.*'        => 'nullable|numeric',
            'light_tax'          => 'nullable|array',
            'light_tax.*'        => 'nullable|numeric',
            'health_tax'         => 'nullable|array',
            'health_tax.*'       => 'nullable|numeric',
            'grand_total'        => 'nullable|array',
            'grand_total.*'      => 'nullable|numeric',
        ]);

        $type = $validated['assessment_type'];
        $fy   = $validated['financial_year'];
        $rowCount = count($validated['sr_no']);

        // Delete old group rows
        if ($assessment->group_id) {
            PropertyAssessment::where('group_id', $assessment->group_id)
                ->where('id', '!=', $assessment->id)->delete();
        }

        $groupId = ($type === 'extended' && $rowCount > 1)
            ? ($assessment->group_id ?? PropertyAssessment::generateGroupId())
            : null;

        // Update the first row
        $assessment->update([
            'assessment_type'          => $type,
            'group_id'                 => $groupId,
            'sr_no'                    => $validated['sr_no'][0],
            'property_number'          => $validated['property_number'][0],
            'description'              => $validated['description'][0] ?? null,
            'owner_name'               => $validated['owner_name'][0],
            'tenant_name'              => ($type === 'rented') ? ($validated['tenant_name'][0] ?? null) : null,
            'year_of_construction'     => $validated['year_of_construction'][0] ?? null,
            'length'                   => $validated['length'][0] ?? null,
            'width'                    => $validated['width'][0] ?? null,
            'square_foot'              => $validated['square_foot'][0] ?? null,
            'square_meter'             => $validated['square_meter'][0] ?? null,
            'rr_rate_land'             => $validated['rr_rate_land'][0] ?? 0,
            'rr_rate_building'         => $validated['rr_rate_building'][0] ?? 0,
            'amount_with_depreciation' => $validated['amount_with_depreciation'][0] ?? 0,
            'total'                    => $validated['total'][0] ?? 0,
            'rate_of_education'        => $validated['rate_of_education'][0] ?? 0,
            'rate_of_bearable'         => $validated['rate_of_bearable'][0] ?? 0,
            'capital_value'            => $validated['capital_value'][0] ?? 0,
            'tax_rate'                 => $validated['tax_rate'][0] ?? 0,
            'house_tax'                => $validated['house_tax'][0] ?? 0,
            'light_tax'                => $validated['light_tax'][0] ?? 0,
            'health_tax'               => $validated['health_tax'][0] ?? 0,
            'grand_total'              => $validated['grand_total'][0] ?? 0,
            'financial_year'           => $fy,
        ]);

        // Create additional rows for extended type
        for ($i = 1; $i < $rowCount; $i++) {
            PropertyAssessment::create([
                'assessment_type'          => $type,
                'group_id'                 => $groupId,
                'sr_no'                    => $validated['sr_no'][$i],
                'property_number'          => $validated['property_number'][$i],
                'description'              => $validated['description'][$i] ?? null,
                'owner_name'               => $validated['owner_name'][$i],
                'tenant_name'              => ($type === 'rented') ? ($validated['tenant_name'][$i] ?? null) : null,
                'year_of_construction'     => $validated['year_of_construction'][$i] ?? null,
                'length'                   => $validated['length'][$i] ?? null,
                'width'                    => $validated['width'][$i] ?? null,
                'square_foot'              => $validated['square_foot'][$i] ?? null,
                'square_meter'             => $validated['square_meter'][$i] ?? null,
                'rr_rate_land'             => $validated['rr_rate_land'][$i] ?? 0,
                'rr_rate_building'         => $validated['rr_rate_building'][$i] ?? 0,
                'amount_with_depreciation' => $validated['amount_with_depreciation'][$i] ?? 0,
                'total'                    => $validated['total'][$i] ?? 0,
                'rate_of_education'        => $validated['rate_of_education'][$i] ?? 0,
                'rate_of_bearable'         => $validated['rate_of_bearable'][$i] ?? 0,
                'capital_value'            => $validated['capital_value'][$i] ?? 0,
                'tax_rate'                 => $validated['tax_rate'][$i] ?? 0,
                'house_tax'                => $validated['house_tax'][$i] ?? 0,
                'light_tax'                => $validated['light_tax'][$i] ?? 0,
                'health_tax'               => $validated['health_tax'][$i] ?? 0,
                'grand_total'              => $validated['grand_total'][$i] ?? 0,
                'financial_year'           => $fy,
            ]);
        }

        return redirect()->route('admin.property-assessments.index')
            ->with('success', 'Assessment updated successfully.');
    }

    /**
     * Delete an assessment (with its group).
     */
    public function destroy($id)
    {
        $assessment = PropertyAssessment::findOrFail($id);

        if ($assessment->group_id) {
            PropertyAssessment::where('group_id', $assessment->group_id)->delete();
        } else {
            $assessment->delete();
        }

        return redirect()->route('admin.property-assessments.index')
            ->with('success', 'Assessment deleted successfully.');
    }

    /**
     * Marathi print view (Form No. 8).
     */
    public function print($id)
    {
        $assessment = PropertyAssessment::findOrFail($id);
        $rows = $assessment->getExtensionRows();

        return view('admin.property-assessments.print', compact('assessment', 'rows'));
    }

    /**
     * Import assessments from CSV/Excel file.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'import_file'     => 'required|file|mimes:csv,txt,xls,xlsx',
            'assessment_type' => 'required|in:regular,rented,extended',
            'financial_year'  => 'required|string|max:10',
        ]);

        $file = $request->file('import_file');
        $type = $request->input('assessment_type');
        $fy   = $request->input('financial_year');

        try {
            $handle = fopen($file->getPathname(), 'r');
            if (!$handle) {
                return back()->with('error', 'Unable to read the uploaded file.');
            }

            // Read header row
            $header = fgetcsv($handle);
            if (!$header) {
                fclose($handle);
                return back()->with('error', 'File is empty or invalid.');
            }

            // Normalize headers (trim + lowercase)
            $header = array_map(function ($h) {
                return strtolower(trim(str_replace(["\xEF\xBB\xBF", "\r", "\n"], '', $h)));
            }, $header);

            // Map expected column names to database fields
            $columnMap = [
                'sr_no'                    => ['sr_no', 'sr. no.', 'sr.no', 'sr no', 'serial', 'a.no', 'a_no'],
                'property_number'          => ['property_number', 'property number', 'property no', 'prop no', 'property no.'],
                'description'              => ['description', 'description of property', 'desc', 'property description'],
                'owner_name'               => ['owner_name', 'owner name', 'name of property owner', 'owner', 'name'],
                'tenant_name'              => ['tenant_name', 'tenant name', 'tenant', 'people who live as tenant'],
                'year_of_construction'     => ['year_of_construction', 'year of construction', 'year', 'construction year'],
                'length'                   => ['length'],
                'width'                    => ['width'],
                'square_foot'              => ['square_foot', 'square foot', 'sq ft', 'sqft', 'sq.ft'],
                'square_meter'             => ['square_meter', 'square meter', 'sq m', 'sqm', 'sq.m'],
                'rr_rate_land'             => ['rr_rate_land', 'rr rate land', 'ready reckoner rate land', 'rr land'],
                'rr_rate_building'         => ['rr_rate_building', 'rr rate building', 'ready reckoner rate building', 'rr building', 'rr bldg'],
                'amount_with_depreciation' => ['amount_with_depreciation', 'amount with depreciation', 'depreciation amount'],
                'total'                    => ['total'],
                'rate_of_education'        => ['rate_of_education', 'rate of education', 'education rate', 'depreciation rate'],
                'rate_of_bearable'         => ['rate_of_bearable', 'rate of bearable', 'bearable rate', 'tax rate bearable'],
                'capital_value'            => ['capital_value', 'capital value'],
                'tax_rate'                 => ['tax_rate', 'tax rate'],
                'house_tax'                => ['house_tax', 'house tax', 'ghar kar'],
                'light_tax'                => ['light_tax', 'light tax', 'electricity tax', 'vidyut kar'],
                'health_tax'               => ['health_tax', 'health tax', 'arogya kar'],
                'grand_total'              => ['grand_total', 'grand total', 'total tax'],
            ];

            // Find column indices
            $indices = [];
            foreach ($columnMap as $field => $aliases) {
                foreach ($aliases as $alias) {
                    $index = array_search($alias, $header);
                    if ($index !== false) {
                        $indices[$field] = $index;
                        break;
                    }
                }
            }

            // Require at minimum sr_no, property_number, owner_name
            $missing = [];
            if (!isset($indices['sr_no'])) $missing[] = 'Sr. No.';
            if (!isset($indices['property_number'])) $missing[] = 'Property Number';
            if (!isset($indices['owner_name'])) $missing[] = 'Owner Name';

            if (!empty($missing)) {
                fclose($handle);
                return back()->with('error', 'Missing required columns: ' . implode(', ', $missing) . '. Please check the CSV header row.');
            }

            $imported = 0;
            $errors = [];
            $rowNum = 1;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;

                // Skip empty rows
                if (empty(array_filter($row))) continue;

                $getValue = function ($field) use ($indices, $row) {
                    if (!isset($indices[$field]) || !isset($row[$indices[$field]])) return null;
                    $val = trim($row[$indices[$field]]);
                    return $val === '' ? null : $val;
                };

                $srNo = $getValue('sr_no');
                $propNo = $getValue('property_number');
                $ownerName = $getValue('owner_name');

                if (!$srNo || !$propNo || !$ownerName) {
                    $errors[] = "Row {$rowNum}: Missing required data (Sr No, Property Number, or Owner Name)";
                    continue;
                }

                try {
                    PropertyAssessment::create([
                        'assessment_type'          => $type,
                        'sr_no'                    => (int)$srNo,
                        'property_number'          => $propNo,
                        'description'              => $getValue('description'),
                        'owner_name'               => $ownerName,
                        'tenant_name'              => ($type === 'rented') ? $getValue('tenant_name') : null,
                        'year_of_construction'     => $getValue('year_of_construction') ? (int)$getValue('year_of_construction') : null,
                        'length'                   => $getValue('length'),
                        'width'                    => $getValue('width'),
                        'square_foot'              => $getValue('square_foot'),
                        'square_meter'             => $getValue('square_meter'),
                        'rr_rate_land'             => $getValue('rr_rate_land') ?? 0,
                        'rr_rate_building'         => $getValue('rr_rate_building') ?? 0,
                        'amount_with_depreciation' => $getValue('amount_with_depreciation') ?? 0,
                        'total'                    => $getValue('total') ?? 0,
                        'rate_of_education'        => $getValue('rate_of_education') ?? 0,
                        'rate_of_bearable'         => $getValue('rate_of_bearable') ?? 0,
                        'capital_value'            => $getValue('capital_value') ?? 0,
                        'tax_rate'                 => $getValue('tax_rate') ?? 0,
                        'house_tax'                => $getValue('house_tax') ?? 0,
                        'light_tax'                => $getValue('light_tax') ?? 0,
                        'health_tax'               => $getValue('health_tax') ?? 0,
                        'grand_total'              => $getValue('grand_total') ?? 0,
                        'financial_year'           => $fy,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNum}: " . $e->getMessage();
                }
            }

            fclose($handle);

            $message = "Successfully imported {$imported} assessment(s).";
            if (!empty($errors)) {
                $message .= ' ' . count($errors) . ' row(s) had errors: ' . implode('; ', array_slice($errors, 0, 5));
            }

            return back()->with($imported > 0 ? 'success' : 'error', $message);

        } catch (\Exception $e) {
            Log::error('Assessment Import Error: ' . $e->getMessage());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download a sample CSV template.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Sr. No.', 'Property Number', 'Description', 'Owner Name', 'Tenant Name',
            'Year of Construction', 'Length', 'Width', 'Square Foot', 'Square Meter',
            'RR Rate Land', 'RR Rate Building', 'Amount with Depreciation', 'Total',
            'Rate of Education', 'Rate of Bearable', 'Capital Value', 'Tax Rate',
            'House Tax', 'Light Tax', 'Health Tax', 'Grand Total'
        ];

        $sampleRow = [
            '1', '53/1', 'Made with Sheets', 'Keshavrao Krishnaji Kale', '',
            '1960', '', '', '160', '15.61',
            '', '14075', '7431.5', '13648',
            '0.5', '1', '21088', '1',
            '213', '20', '20', '253'
        ];

        $callback = function () use ($headers, $sampleRow) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers);
            fputcsv($file, $sampleRow);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="property_assessment_template.csv"',
        ]);
    }
}

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>मूल्यांकन यादी - {{ $assessment->property_number }}</title>
    <style>
        @page {
            size: landscape;
            margin: 12mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Noto Sans Devanagari', 'Mangal', sans-serif;
            font-size: 12px;
            color: #000;
            background: white;
            line-height: 1.5;
        }

        .print-container { max-width: 100%; padding: 20px; }

        /* Header */
        .header { text-align: center; margin-bottom: 20px; }
        .header .village-label { font-size: 11px; color: #555; margin-bottom: 2px; }
        .header .panchayat-name { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .header .sub-info { font-size: 11px; color: #444; margin-bottom: 8px; }
        .header .form-title {
            display: inline-block;
            background: #16a34a;
            color: white;
            padding: 6px 24px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .header .fy-info { font-size: 13px; font-weight: 600; }

        /* Assessment Type Badge */
        .type-info {
            text-align: center;
            margin-bottom: 16px;
        }
        .type-badge {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
        }
        .type-badge.regular { background: #dcfce7; color: #16a34a; }
        .type-badge.rented { background: #dbeafe; color: #2563eb; }
        .type-badge.extended { background: #ffedd5; color: #ea580c; }

        /* Table */
        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .assessment-table th,
        .assessment-table td {
            border: 1.5px solid #333;
            padding: 6px 8px;
            text-align: center;
            font-size: 11px;
            vertical-align: middle;
        }
        .assessment-table th {
            background: #f0fdf4;
            font-weight: 700;
            font-size: 10px;
        }
        .assessment-table td {
            font-size: 12px;
        }
        .assessment-table .col-label-mr {
            font-size: 10px;
            font-weight: 700;
        }
        .assessment-table .col-label-en {
            font-size: 8px;
            color: #555;
            display: block;
        }

        /* Footer */
        .footer { margin-top: 40px; display: flex; justify-content: space-between; align-items: flex-end; }
        .footer .left { text-align: left; }
        .footer .right { text-align: center; }
        .signature-line { border-top: 1px solid #333; padding-top: 4px; min-width: 200px; font-size: 12px; font-weight: 600; }
        .seal-area { margin-top: 20px; font-size: 10px; color: #888; }

        /* Print */
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-container { padding: 0; }
        }

        .print-btn {
            position: fixed; top: 20px; right: 20px;
            background: #7c3aed; color: white;
            border: none; padding: 12px 24px; border-radius: 8px;
            font-size: 14px; cursor: pointer; z-index: 100;
            box-shadow: 0 4px 12px rgba(124,58,237,0.3);
        }
        .print-btn:hover { background: #6d28d9; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> प्रिंट करा / Print
    </button>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <div class="village-label">ना. कैलाश</div>
            <div class="panchayat-name">ग्रामपंचायत, नेरळ</div>
            <div class="sub-info">राजमाला जिजाई भोसले तलावा जवळ, नेरळ, ता. कर्जत, जि. रायगड</div>
            <div class="form-title">नमुना क्र. ८ मूल्यांकन यादी नोंदवही (Form No. 8 Assessment List Register)</div>
            <div class="fy-info">
                गाव नेरळ मूल्यांकन यादी नोंदवही सन {{ $assessment->financial_year }} करपात्र इमारती व जमिनीसाठी
            </div>
            <div style="font-size:11px;color:#555;margin-top:2px;">
                Village Name Neral Assessment List Register of Year {{ $assessment->financial_year }} for the Taxable Buildings and Lands
            </div>
        </div>

        <!-- Type -->
        <div class="type-info">
            <span class="type-badge {{ $assessment->assessment_type }}">
                {{ $assessment->type_marathi }}
                ({{ ucfirst($assessment->assessment_type) }})
            </span>
        </div>

        <!-- Assessment Table -->
        <table class="assessment-table">
            <thead>
                <tr>
                    <th>
                        <span class="col-label-mr">अ.क्र.</span>
                        <span class="col-label-en">Sr. No.</span>
                    </th>
                    <th>
                        <span class="col-label-mr">मालमत्ता क्र.</span>
                        <span class="col-label-en">Property Number</span>
                    </th>
                    <th>
                        <span class="col-label-mr">मालमत्तेचे वर्णन</span>
                        <span class="col-label-en">Description of Property</span>
                    </th>
                    <th>
                        <span class="col-label-mr">मालमत्ता धारकाचे नाव</span>
                        <span class="col-label-en">Name of Property Owner</span>
                    </th>
                    @if($assessment->assessment_type === 'rented')
                    <th>
                        <span class="col-label-mr">भाडेकरूचे नाव</span>
                        <span class="col-label-en">Tenant Name</span>
                    </th>
                    @endif
                    <th>
                        <span class="col-label-mr">बांधकाम वर्ष</span>
                        <span class="col-label-en">Year of Construction</span>
                    </th>
                    <th>
                        <span class="col-label-mr">लांबी</span>
                        <span class="col-label-en">Length</span>
                    </th>
                    <th>
                        <span class="col-label-mr">रुंदी</span>
                        <span class="col-label-en">Width</span>
                    </th>
                    <th>
                        <span class="col-label-mr">चौ.फूट</span>
                        <span class="col-label-en">Square Foot</span>
                    </th>
                    <th>
                        <span class="col-label-mr">चौ.मी.</span>
                        <span class="col-label-en">Square Meter</span>
                    </th>
                    <th>
                        <span class="col-label-mr">रेडी रेकनर दर - जमीन</span>
                        <span class="col-label-en">RR Rate – Land</span>
                    </th>
                    <th>
                        <span class="col-label-mr">रेडी रेकनर दर - बांधकाम</span>
                        <span class="col-label-en">RR Rate – Building</span>
                    </th>
                    <th>
                        <span class="col-label-mr">घसारा रक्कम</span>
                        <span class="col-label-en">Amount with Depreciation</span>
                    </th>
                    <th>
                        <span class="col-label-mr">एकूण</span>
                        <span class="col-label-en">Total</span>
                    </th>
                    <th>
                        <span class="col-label-mr">घसारा दर</span>
                        <span class="col-label-en">Rate of Education</span>
                    </th>
                    <th>
                        <span class="col-label-mr">सहनीय दर</span>
                        <span class="col-label-en">Rate of Bearable</span>
                    </th>
                    <th>
                        <span class="col-label-mr">भांडवली मूल्य</span>
                        <span class="col-label-en">Capital Value</span>
                    </th>
                    <th>
                        <span class="col-label-mr">कर दर</span>
                        <span class="col-label-en">Tax Rate</span>
                    </th>
                    <th>
                        <span class="col-label-mr">घर कर</span>
                        <span class="col-label-en">House Tax</span>
                    </th>
                    <th>
                        <span class="col-label-mr">विद्युत कर</span>
                        <span class="col-label-en">Light Tax</span>
                    </th>
                    <th>
                        <span class="col-label-mr">आरोग्य कर</span>
                        <span class="col-label-en">Health Tax</span>
                    </th>
                    <th>
                        <span class="col-label-mr">एकूण</span>
                        <span class="col-label-en">Total</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                <tr>
                    <td>{{ $row->sr_no }}</td>
                    <td><strong>{{ $row->property_number }}</strong></td>
                    <td>{{ $row->description ?? '-' }}</td>
                    <td>{{ $row->owner_name }}</td>
                    @if($assessment->assessment_type === 'rented')
                    <td>{{ $row->tenant_name ?? '-' }}</td>
                    @endif
                    <td>{{ $row->year_of_construction ?? '-' }}</td>
                    <td>{{ $row->length ? number_format($row->length, 0) : '' }}</td>
                    <td>{{ $row->width ? number_format($row->width, 0) : '' }}</td>
                    <td>{{ $row->square_foot ? number_format($row->square_foot, 0) : '' }}</td>
                    <td>{{ $row->square_meter ? number_format($row->square_meter, 2) : '' }}</td>
                    <td>{{ $row->rr_rate_land ? number_format($row->rr_rate_land, 0) : '' }}</td>
                    <td>{{ $row->rr_rate_building ? number_format($row->rr_rate_building, 0) : '' }}</td>
                    <td>{{ $row->amount_with_depreciation ? number_format($row->amount_with_depreciation, 1) : '' }}</td>
                    <td>{{ $row->total ? number_format($row->total, 0) : '' }}</td>
                    <td>{{ $row->rate_of_education ?: '' }}</td>
                    <td>{{ $row->rate_of_bearable ?: '' }}</td>
                    <td>{{ $row->capital_value ? number_format($row->capital_value, 0) : '' }}</td>
                    <td>{{ $row->tax_rate ?: '' }}</td>
                    <td><strong>{{ $row->house_tax ? number_format($row->house_tax, 0) : '' }}</strong></td>
                    <td>{{ $row->light_tax ? number_format($row->light_tax, 0) : '' }}</td>
                    <td>{{ $row->health_tax ? number_format($row->health_tax, 0) : '' }}</td>
                    <td><strong>{{ $row->grand_total ? number_format($row->grand_total, 0) : '' }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <div class="left">
                <div style="font-size:12px;margin-bottom:30px;">
                    मूल्यांकन नोंदवहीची खरी प्रत<br>
                    <span style="font-size:10px;color:#666;">Issued True copy of Assessment Register on</span>
                </div>
                <div>
                    <div class="signature-line">
                        भरणा करणाऱ्याची सही<br>
                        <span style="font-size:10px;color:#666;">(Payer Signature)</span>
                    </div>
                </div>
            </div>
            <div class="right">
                <div class="seal-area" style="margin-bottom:20px;">[शिक्का / Seal]</div>
                <div class="signature-line">
                    ग्रामसेवक / <br>
                    ग्रामपंचायत, नेरळ
                </div>
            </div>
        </div>
    </div>
</body>
</html>

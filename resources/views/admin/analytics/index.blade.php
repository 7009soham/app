@extends('admin.layouts.app')

@section('title', 'Analytics')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Tax Analytics Dashboard</h4>
        </div>
    </div>
</div>

<!-- Monthly Performance Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="fas fa-chart-bar me-2"></i> Monthly Collection Performance
                </h5>
                <div class="text-muted small">Target vs Actual Collection (Last 12 Months)</div>
            </div>
            <div class="card-body">
                <div style="height: 350px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="fas fa-table me-2"></i> Demand-wise Analytics
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Demand</th>
                                <th>Citizens</th>
                                <th>Water Paid</th>
                                <th>Property Paid</th>
                                <th>Total Paid</th>
                                <th>Water Pending</th>
                                <th>Property Pending</th>
                                <th>Total Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($demandData as $data)
                            <tr>
                                <td>
                                    <span class="badge rounded-pill bg-primary px-3 py-2">
                                        {{ $data['demand_name'] ?? ($data['demand_id'] ?? '-') }}
                                    </span>
                                </td>
                                <td class="fw-medium">{{ number_format($data['total_citizens']) }}</td>
                                <td class="text-success small">₹{{ number_format($data['water_tax_paid'], 2) }}</td>
                                <td class="text-success small">₹{{ number_format($data['property_tax_paid'], 2) }}</td>
                                <td class="text-success fw-bold">₹{{ number_format($data['total_paid'], 2) }}</td>
                                <td class="text-danger small">₹{{ number_format($data['water_balance'], 2) }}</td>
                                <td class="text-danger small">₹{{ number_format($data['property_balance'], 2) }}</td>
                                <td class="text-danger fw-bold">₹{{ number_format($data['total_balance'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold border-top-2">
                            <tr>
                                <td>Total</td>
                                <td>{{ number_format(collect($demandData)->sum('total_citizens')) }}</td>
                                <td>₹{{ number_format(collect($demandData)->sum('water_tax_paid'), 2) }}</td>
                                <td>₹{{ number_format(collect($demandData)->sum('property_tax_paid'), 2) }}</td>
                                <td class="text-primary">₹{{ number_format(collect($demandData)->sum('total_paid'), 2) }}</td>
                                <td>₹{{ number_format(collect($demandData)->sum('water_balance'), 2) }}</td>
                                <td>₹{{ number_format(collect($demandData)->sum('property_balance'), 2) }}</td>
                                <td class="text-danger">₹{{ number_format(collect($demandData)->sum('total_balance'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        
        const data = {
            labels: {!! json_encode(collect($monthlyAnalytics)->pluck('month')) !!},
            datasets: [
                {
                    label: 'Actual Collection (₹)',
                    data: {!! json_encode(collect($monthlyAnalytics)->pluck('collected')) !!},
                    backgroundColor: 'rgba(25, 135, 84, 0.7)',
                    borderColor: 'rgb(25, 135, 84)',
                    borderWidth: 1,
                    type: 'bar',
                    order: 2,
                    borderRadius: 5,
                },
                {
                    label: 'Target Billed (₹)',
                    data: {!! json_encode(collect($monthlyAnalytics)->pluck('billed')) !!},
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: 'rgb(13, 110, 253)',
                    pointRadius: 4,
                    type: 'line',
                    fill: true,
                    tension: 0.4,
                    order: 1
                }
            ]
        };

        new Chart(ctx, {
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        padding: 12,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14,
                            family: "'Inter', sans-serif"
                        },
                        bodyFont: {
                            size: 13,
                            family: "'Inter', sans-serif"
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: "'Inter', sans-serif"
                            },
                            callback: function(value) {
                                if (value >= 1000) {
                                    return '₹' + value / 1000 + 'k';
                                }
                                return '₹' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: "'Inter', sans-serif"
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush

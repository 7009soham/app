@extends('admin.layouts.app')

@section('title', 'Tax Collection')

@section('header', 'Tax Collection')

@section('content')

{{-- Filter Bar --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.tax-collection.index') }}" method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Year (Water Tax)</label>
                <select name="year" class="form-select">
                    @foreach(range(date('Y')-2, date('Y')+1) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Month (Water Tax)</label>
                <select name="month" class="form-select">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Demand</label>
                <select name="demand_id" class="form-select">
                    <option value="">All</option>
                    @foreach($demands as $demand)
                        <option value="{{ $demand->id }}" {{ (string) request('demand_id') === (string) $demand->id ? 'selected' : '' }}>
                            {{ $demand->name ?? ('Demand ' . $demand->id) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name / No" value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-0" id="taxTabs" role="tablist">
    @if($canViewWater)
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="water-tab" data-bs-toggle="tab" data-bs-target="#water-pane"
            type="button" role="tab">
            <i class="fas fa-tint me-1 text-info"></i> Water Tax (Monthly)
            <span class="badge bg-info ms-1">{{ $waterBills instanceof \Illuminate\Pagination\LengthAwarePaginator ? $waterBills->total() : $waterBills->count() }}</span>
        </button>
    </li>
    @endif
    @if($canViewProperty)
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ !$canViewWater ? 'active' : '' }}" id="property-tab" data-bs-toggle="tab"
            data-bs-target="#property-pane" type="button" role="tab">
            <i class="fas fa-home me-1 text-success"></i> Property Tax (Annual FY {{ $fy }})
            <span class="badge bg-success ms-1">{{ $propertyBills instanceof \Illuminate\Pagination\LengthAwarePaginator ? $propertyBills->total() : $propertyBills->count() }}</span>
        </button>
    </li>
    @endif
</ul>

<div class="tab-content border border-top-0 rounded-bottom bg-white shadow-sm" id="taxTabsContent">

    {{-- ═══════════════════════════ WATER TAX TAB ═══════════════════════════ --}}
    @if($canViewWater)
    <div class="tab-pane fade show active" id="water-pane" role="tabpanel">
        <div class="p-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-info">
                <i class="fas fa-tint me-1"></i>
                Water Tax Bills for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
            </h6>
            <div class="d-flex gap-2">
                <select id="waterBulkAction" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Bulk Actions</option>
                    <option value="mark_paid">Mark as Paid</option>
                    <option value="send_reminder">Send Reminder</option>
                </select>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="applyTaxCollectionBulk('water')">Apply</button>
                @if(auth('admin')->user()->isSuperAdmin())
                <form action="{{ route('admin.tax-collection.fast-forward') }}" method="POST"
                    onsubmit="return confirm('Fast forward water tax to next month?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning text-dark">
                        <i class="fas fa-forward me-1"></i> Time Travel (Water)
                    </button>
                </form>
                @endif
                <form action="{{ route('admin.tax-collection.store-bills') }}" method="POST"
                    onsubmit="return confirm('Generate water tax bills for {{ date('F Y', mktime(0,0,0,$month,1,$year)) }}?');">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-plus-circle me-1"></i> Generate Monthly Bills
                    </button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="waterSelectAll" onchange="toggleAll('water')"></th>
                        <th>Customer</th>
                        <th>Bill Period</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Penalty</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($waterBills as $bill)
                    <tr>
                        <td><input type="checkbox" class="water-row-checkbox" value="{{ $bill->id }}"></td>
                        <td>
                            <div class="fw-bold">{{ $bill->customer_name }}</div>
                            <div class="small text-muted">{{ $bill->customer_no }}</div>
                        </td>
                        <td>{{ $bill->month_name }} {{ $bill->bill_year }}</td>
                        <td>₹{{ number_format($bill->bill_amount, 2) }}</td>
                        <td class="text-success">₹{{ number_format($bill->paid_amount, 2) }}</td>
                        <td class="text-danger fw-bold">₹{{ number_format($bill->balance, 2) }}</td>
                        <td>
                            @if($bill->penalty_amount > 0)
                                <span class="text-danger fw-bold">₹{{ number_format($bill->penalty_amount, 2) }}</span>
                                <br><small class="text-muted">{{ $bill->penalty_percentage }}%</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge
                                {{ $bill->status == 'paid' ? 'bg-success' : '' }}
                                {{ $bill->status == 'pending' ? 'bg-warning text-dark' : '' }}
                                {{ $bill->status == 'partial' ? 'bg-info' : '' }}
                                {{ $bill->status == 'overdue' ? 'bg-danger' : '' }}">
                                {{ ucfirst($bill->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($bill->balance > 0)
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                    data-bs-target="#payWaterModal{{ $bill->id }}">
                                    <i class="fas fa-money-bill-wave"></i> Pay
                                </button>
                                @endif
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#statusWaterModal{{ $bill->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>

                            {{-- Pay Modal --}}
                            @if($bill->balance > 0)
                            <div class="modal fade" id="payWaterModal{{ $bill->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.tax-collection.pay', $bill) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Record Payment: {{ $bill->customer_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted small mb-3">Water Tax for {{ $bill->month_name }} {{ $bill->bill_year }}</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">₹</span>
                                                        <input type="number" name="amount" class="form-control"
                                                            step="0.01" min="1" max="{{ $bill->balance }}"
                                                            value="{{ $bill->balance }}" required>
                                                    </div>
                                                    <small class="text-muted">Balance: ₹{{ $bill->balance }}</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                                    <select name="payment_method" class="form-select" required>
                                                        <option value="cash">Cash</option>
                                                        <option value="online">Online</option>
                                                        <option value="cheque">Cheque</option>
                                                        <option value="bank_transfer">Bank Transfer</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Remarks</label>
                                                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Record Payment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Status Modal --}}
                            <div class="modal fade" id="statusWaterModal{{ $bill->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.tax-collection.update-status', $bill) }}" method="POST">
                                            @csrf @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-info"><small>Changing status only does not record a payment.</small></div>
                                                <select name="status" class="form-select" required>
                                                    <option value="pending" {{ $bill->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="partial" {{ $bill->status == 'partial' ? 'selected' : '' }}>Partial</option>
                                                    <option value="paid" {{ $bill->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                                    <option value="overdue" {{ $bill->status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                                </select>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-tint fa-3x mb-3 d-block"></i>
                            No water tax bills for {{ date('F Y', mktime(0,0,0,$month,1,$year)) }}.
                            Click <strong>Generate Monthly Bills</strong> above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($waterBills instanceof \Illuminate\Pagination\LengthAwarePaginator && $waterBills->hasPages())
        <div class="p-3">{{ $waterBills->links() }}</div>
        @endif
    </div>
    @endif

    {{-- ═══════════════════════════ PROPERTY TAX TAB ═══════════════════════ --}}
    @if($canViewProperty)
    <div class="tab-pane fade {{ !$canViewWater ? 'show active' : '' }}" id="property-pane" role="tabpanel">
        <div class="p-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-success">
                <i class="fas fa-home me-1"></i>
                Property Tax Annual Bills for FY {{ $fy }}
                <small class="text-muted fw-normal">(1 Apr {{ substr($fy,0,4) }} to 31 Mar {{ (int)substr($fy,0,4)+1 }})</small>
            </h6>
            <div class="d-flex gap-2">
                <select id="propertyBulkAction" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Bulk Actions</option>
                    <option value="mark_paid">Mark as Paid</option>
                    <option value="send_reminder">Send Reminder</option>
                </select>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="applyTaxCollectionBulk('property')">Apply</button>
                <form action="{{ route('admin.tax-collection.generate-annual-property') }}" method="POST"
                onsubmit="return confirm('Generate annual property tax bills for FY {{ $fy }}? Each property gets exactly ONE bill. This cannot be undone.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-calendar-check me-1"></i> Generate Annual Property Bills (FY {{ $fy }})
                </button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="propertySelectAll" onchange="toggleAll('property')"></th>
                        <th>Customer</th>
                        <th>Property No</th>
                        <th>Bill No</th>
                        <th>House Tax</th>
                        <th>Elec. Tax</th>
                        <th>Health Tax</th>
                        <th>Total Bill</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($propertyBills as $bill)
                    <tr>
                        <td><input type="checkbox" class="property-row-checkbox" value="{{ $bill->id }}"></td>
                        <td>
                            <div class="fw-bold">{{ $bill->customer_name }}</div>
                            <div class="small text-muted">{{ $bill->customer_no }}</div>
                        </td>
                        <td class="small">{{ $bill->propertyTaxRecord->property_no ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $bill->bill_no ?? '-' }}</span></td>
                        <td>₹{{ number_format($bill->house_tax, 2) }}</td>
                        <td>₹{{ number_format($bill->electricity_tax, 2) }}</td>
                        <td>₹{{ number_format($bill->health_tax, 2) }}</td>
                        <td class="fw-bold">₹{{ number_format($bill->bill_amount, 2) }}</td>
                        <td class="text-success">₹{{ number_format($bill->paid_amount, 2) }}</td>
                        <td class="text-danger fw-bold">₹{{ number_format($bill->balance, 2) }}</td>
                        <td class="small">{{ $bill->due_date ? $bill->due_date->format('d M Y') : '-' }}</td>
                        <td>
                            <span class="badge
                                {{ $bill->status == 'paid' ? 'bg-success' : '' }}
                                {{ $bill->status == 'pending' ? 'bg-warning text-dark' : '' }}
                                {{ $bill->status == 'partial' ? 'bg-info' : '' }}
                                {{ $bill->status == 'overdue' ? 'bg-danger' : '' }}">
                                {{ ucfirst($bill->status) }}
                            </span>
                        </td>
                        <td>
                            @if($bill->balance > 0)
                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                data-bs-target="#payPropertyModal{{ $bill->id }}">
                                <i class="fas fa-money-bill-wave"></i> Pay
                            </button>
                            @else
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Paid</span>
                            @endif

                            {{-- Property Pay Modal --}}
                            @if($bill->balance > 0)
                            <div class="modal fade" id="payPropertyModal{{ $bill->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.tax-collection.property-annual.pay', $bill) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Record Payment: {{ $bill->customer_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted small mb-2">
                                                    Property Tax FY {{ $bill->financial_year }} &bull; {{ $bill->bill_no }}
                                                </p>
                                                <table class="table table-sm mb-3">
                                                    <tr><td>House Tax</td><td class="text-end">₹{{ $bill->house_tax }}</td></tr>
                                                    <tr><td>Electricity Tax</td><td class="text-end">₹{{ $bill->electricity_tax }}</td></tr>
                                                    <tr><td>Health Tax</td><td class="text-end">₹{{ $bill->health_tax }}</td></tr>
                                                    <tr class="table-light fw-bold"><td>Total</td><td class="text-end">₹{{ $bill->bill_amount }}</td></tr>
                                                    <tr><td>Already Paid</td><td class="text-end text-success">₹{{ $bill->paid_amount }}</td></tr>
                                                    <tr class="table-warning fw-bold"><td>Balance Due</td><td class="text-end text-danger">₹{{ $bill->balance }}</td></tr>
                                                </table>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">₹</span>
                                                        <input type="number" name="amount" class="form-control"
                                                            step="0.01" min="1" max="{{ $bill->balance }}"
                                                            value="{{ $bill->balance }}" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                                    <select name="payment_method" class="form-select" required>
                                                        <option value="cash">Cash</option>
                                                        <option value="online">Online</option>
                                                        <option value="cheque">Cheque</option>
                                                        <option value="bank_transfer">Bank Transfer</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Remarks</label>
                                                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Record Payment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center py-5 text-muted">
                            <i class="fas fa-home fa-3x mb-3 d-block"></i>
                            No property tax annual bills for FY {{ $fy }}.
                            <br>Click <strong>Generate Annual Property Bills</strong> above to create them.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($propertyBills instanceof \Illuminate\Pagination\LengthAwarePaginator && $propertyBills->hasPages())
        <div class="p-3">{{ $propertyBills->links() }}</div>
        @endif
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
    function toggleAll(type) {
        let isChecked = document.getElementById(type + 'SelectAll').checked;
        let checkboxes = document.querySelectorAll('.' + type + '-row-checkbox');
        checkboxes.forEach(cb => cb.checked = isChecked);
    }
    
    function applyTaxCollectionBulk(type) {
        let action = document.getElementById(type + 'BulkAction').value;
        if (!action) {
            alert('Please select a bulk action');
            return;
        }
        
        let selected = [];
        document.querySelectorAll('.' + type + '-row-checkbox:checked').forEach(cb => selected.push(cb.value));
        
        if (selected.length === 0) {
            alert('Please select at least one record');
            return;
        }
        
        if (confirm('Are you sure you want to ' + action + ' ' + selected.length + ' records?')) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = type === 'water' ? '{{ route("admin.tax-collection.bulk-water") }}' : '{{ route("admin.tax-collection.bulk-property") }}';
            
            let csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            let actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);

            selected.forEach(id => {
                let idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'ids[]';
                idInput.value = id;
                form.appendChild(idInput);
            });

            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush

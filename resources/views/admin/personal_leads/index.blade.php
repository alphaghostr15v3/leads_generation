@extends('layouts.admin')

@section('title', 'Personal Leads Management')

@section('content')
<div class="row mb-3">
    <div class="col">
        <h2><i class="bi bi-person-vcard"></i> Personal Leads Management</h2>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.personal-leads.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Personal Lead
        </a>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
            <i class="bi bi-download"></i> Export
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.personal-leads.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" 
                       placeholder="Search by name, address, phone...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Per Page</label>
                <select name="per_page" class="form-select">
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 100) == 100 ? 'selected' : '' }}>100</option>
                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                    <option value="1000" {{ request('per_page') == 1000 ? 'selected' : '' }}>1000</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('admin.personal-leads.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Leads Table -->
<form id="bulkDeleteForm" action="{{ route('admin.personal-leads.bulk-delete') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Personal Leads List</h5>
            <button type="submit" id="bulkDeleteBtn" class="btn btn-danger btn-sm d-none" 
                    onclick="return confirm('Are you sure you want to delete the selected personal leads?')">
                <i class="bi bi-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>S.No</th>
                            <!-- <th>ID</th> -->
                            <th>Name</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Website</th>
                            <th>Review</th>
                            <!-- <th>Created At</th> -->
                            <th style="min-width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr>
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $lead->id }}" class="form-check-input lead-checkbox">
                                </td>
                                <td>{{ ($leads->currentPage() - 1) * $leads->perPage() + $loop->iteration }}</td>
                                <!-- <td>{{ $lead->id }}</td> -->
                                <td>{{ $lead->name }}</td>
                                <td>{{ Str::limit($lead->address, 50) }}</td>
                                <td>{{ $lead->phone }}</td>
                                <td>
                                    @if($lead->website)
                                        <a href="{{ $lead->website }}" target="_blank" title="{{ $lead->website }}">
                                            <i class="bi bi-link-45deg"></i> Link
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ Str::limit($lead->review, 50) }}</td>
                                <!-- <td>{{ optional($lead->created_at)->format('Y-m-d') ?? 'N/A' }}</td> -->
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.personal-leads.edit', $lead) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                                data-url="{{ route('admin.personal-leads.destroy', $lead) }}"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No personal leads found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $leads->firstItem() ?? 0 }} to {{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }} personal leads
                </div>
                <div>
                    {{ $leads->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Single Delete Form -->
<form id="singleDeleteForm" action="" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bulk Delete functionality
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.lead-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const selectedCount = document.getElementById('selectedCount');

        function updateBulkDeleteButton() {
            const checkedCount = document.querySelectorAll('.lead-checkbox:checked').length;
            selectedCount.textContent = checkedCount;
            
            if (checkedCount > 0) {
                bulkDeleteBtn.classList.remove('d-none');
            } else {
                bulkDeleteBtn.classList.add('d-none');
            }
            
            selectAll.checked = (checkedCount === checkboxes.length && checkboxes.length > 0);
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                updateBulkDeleteButton();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkDeleteButton);
        });

        // Single Delete functionality
        const deleteBtns = document.querySelectorAll('.delete-btn');
        const singleDeleteForm = document.getElementById('singleDeleteForm');

        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this personal lead?')) {
                    singleDeleteForm.action = this.dataset.url;
                    singleDeleteForm.submit();
                }
            });
        });
    });
</script>
@endpush

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.export') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="personal">
                <div class="modal-header">
                    <h5 class="modal-title">Export Personal Leads</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select" required>
                            <option value="xlsx">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Columns</label>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="id" checked>
                                    <label class="form-check-label">ID</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="name" checked>
                                    <label class="form-check-label">Name</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="address" checked>
                                    <label class="form-check-label">Address</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="phone" checked>
                                    <label class="form-check-label">Phone</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="website">
                                    <label class="form-check-label">Website</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="review">
                                    <label class="form-check-label">Review</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="created_at">
                                    <label class="form-check-label">Created At</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pass current filters -->
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-download"></i> Download
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

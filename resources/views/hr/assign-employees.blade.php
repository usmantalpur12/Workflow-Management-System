@extends('layouts.app')

@section('title', 'Assign Employees to Projects')

@section('content')
<div class="container-fluid mt-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 fw-bold mb-2">
                        <i class="fas fa-users-cog me-2"></i>
                        Assign Employees to Projects
                    </h1>
                    <p class="lead mb-0">Manage employee assignments for department projects</p>
                </div>
                <div>
                    <a href="{{ route('hr.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-building me-2"></i>
                        Department: {{ $department->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stat-item">
                                <i class="fas fa-users text-primary"></i>
                                <span class="stat-label">Total Employees:</span>
                                <span class="stat-value">{{ $employees->count() }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <i class="fas fa-project-diagram text-success"></i>
                                <span class="stat-label">Active Projects:</span>
                                <span class="stat-value">{{ $projects->count() }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <i class="fas fa-user-check text-warning"></i>
                                <span class="stat-label">Assigned Employees:</span>
                                <span class="stat-value">{{ $projects->sum(function($project) { return $project->users()->count(); }) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Assign Employees to Project
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('hr.projects.assign-employees') }}" method="POST" id="assignForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="project_id" class="form-label">
                                        <i class="fas fa-project-diagram me-1"></i>
                                        Select Project
                                    </label>
                                    <select name="project_id" id="project_id" class="form-select" required>
                                        <option value="">Choose a project...</option>
                                        @foreach($projects as $project)
                                        <option value="{{ $project->id }}" 
                                                data-status="{{ $project->status }}"
                                                data-start="{{ $project->start_date->format('M d, Y') }}"
                                                data-end="{{ $project->end_date->format('M d, Y') }}">
                                            {{ $project->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Select the project to assign employees to</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Project Details
                                    </label>
                                    <div id="projectDetails" class="form-control-plaintext">
                                        <small class="text-muted">Select a project to view details</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="user_ids" class="form-label">
                                <i class="fas fa-users me-1"></i>
                                Select Employees
                            </label>
                            <div class="row">
                                @foreach($employees as $employee)
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="user_ids[]" 
                                               value="{{ $employee->id }}" 
                                               id="employee_{{ $employee->id }}">
                                        <label class="form-check-label" for="employee_{{ $employee->id }}">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $employee->profile_picture_url }}" 
                                                     alt="{{ $employee->name }}" 
                                                     class="rounded-circle me-2" 
                                                     style="width: 24px; height: 24px; object-fit: cover;">
                                                <div>
                                                    <div class="fw-medium">{{ $employee->name }}</div>
                                                    <small class="text-muted">{{ ucfirst($employee->role) }}</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="form-text">Select one or more employees to assign to the project</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="selectAll()">
                                    <i class="fas fa-check-double me-1"></i>Select All
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="selectNone()">
                                    <i class="fas fa-times me-1"></i>Select None
                                </button>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary" id="assignBtn">
                                    <i class="fas fa-user-plus me-2"></i>Assign Employees
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Project Status -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Project Status
                    </h5>
                </div>
                <div class="card-body">
                    @if($projects->count() > 0)
                        @foreach($projects as $project)
                        <div class="project-status-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $project->name }}</h6>
                                    <small class="text-muted">
                                        {{ $project->start_date->format('M d') }} - {{ $project->end_date->format('M d, Y') }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $project->status_color }} mb-1">
                                        {{ ucfirst($project->status) }}
                                    </span>
                                    <div>
                                        <small class="text-muted">
                                            {{ $project->users()->count() }} employees
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-project-diagram fa-3x mb-3"></i>
                            <p>No projects available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.stat-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 8px;
    margin-bottom: 1rem;
}

.stat-item i {
    font-size: 1.5rem;
    margin-right: 0.75rem;
}

.stat-label {
    font-weight: 500;
    margin-right: 0.5rem;
}

.stat-value {
    font-weight: 700;
    font-size: 1.25rem;
}

.project-status-item {
    padding: 1rem;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.form-check-label {
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: background-color 0.2s;
}

.form-check-label:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.form-check-input:checked + .form-check-label {
    background-color: rgba(0, 123, 255, 0.2);
}

#projectDetails {
    min-height: 40px;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
}

.card {
    border: none;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
}

.card-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border-radius: 12px 12px 0 0 !important;
    border: none;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('project_id');
    const projectDetails = document.getElementById('projectDetails');
    const assignForm = document.getElementById('assignForm');
    const assignBtn = document.getElementById('assignBtn');

    // Update project details when project is selected
    projectSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const status = selectedOption.dataset.status;
            const start = selectedOption.dataset.start;
            const end = selectedOption.dataset.end;
            
            projectDetails.innerHTML = `
                <div class="row">
                    <div class="col-6">
                        <strong>Status:</strong> <span class="badge bg-${getStatusColor(status)}">${status}</span>
                    </div>
                    <div class="col-6">
                        <strong>Duration:</strong> ${start} - ${end}
                    </div>
                </div>
            `;
        } else {
            projectDetails.innerHTML = '<small class="text-muted">Select a project to view details</small>';
        }
    });

    // Handle form submission
    assignForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selectedEmployees = document.querySelectorAll('input[name="user_ids[]"]:checked');
        if (selectedEmployees.length === 0) {
            alert('Please select at least one employee');
            return;
        }

        const formData = new FormData(this);
        const originalText = assignBtn.innerHTML;
        
        // Show loading state
        assignBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Assigning...';
        assignBtn.disabled = true;
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Employees assigned successfully!');
                // Reset form
                this.reset();
                projectDetails.innerHTML = '<small class="text-muted">Select a project to view details</small>';
            } else {
                alert('Error: ' + (data.message || 'Failed to assign employees'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error assigning employees. Please try again.');
        })
        .finally(() => {
            // Reset button
            assignBtn.innerHTML = originalText;
            assignBtn.disabled = false;
        });
    });
});

function selectAll() {
    const checkboxes = document.querySelectorAll('input[name="user_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function selectNone() {
    const checkboxes = document.querySelectorAll('input[name="user_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}

function getStatusColor(status) {
    const colors = {
        'planning': 'secondary',
        'active': 'success',
        'completed': 'primary',
        'on_hold': 'warning',
        'cancelled': 'danger'
    };
    return colors[status] || 'secondary';
}
</script>
@endpush
@endsection

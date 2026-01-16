@extends('layouts.app')

@section('title', 'HR Dashboard')

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2 fw-bold text-gradient">
                <i class="fas fa-users-cog me-2"></i>
                HR Dashboard
            </h1>
            <p class="text-muted mb-0">Welcome back, <strong>{{ Auth::user()->name }}</strong>! Manage your department efficiently.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hr.assign-employees') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-user-plus"></i>
                <span>Assign Employees</span>
            </a>
            <a href="{{ route('projects.create') }}" class="btn-modern btn-modern-secondary">
                <i class="fas fa-plus"></i>
                <span>New Project</span>
            </a>
        </div>
    </div>

    <!-- Department Stats -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon" style="background: var(--primary-100); color: var(--primary-600);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card-value">{{ $department->users()->count() }}</div>
                <div class="stat-card-label">Department Employees</div>
                <div class="stat-card-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>Active</span>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon" style="background: var(--success-light); color: var(--success);">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="stat-card-value">{{ $department->projects()->count() }}</div>
                <div class="stat-card-label">Active Projects</div>
                <div class="stat-card-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>Running</span>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon" style="background: var(--warning-light); color: var(--warning);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-value">{{ $department->projects()->where('status', 'in_progress')->count() }}</div>
                <div class="stat-card-label">Projects In Progress</div>
                <div class="stat-card-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>Ongoing</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card-modern">
        <div class="card-modern-header">
            <h5 class="card-modern-title mb-0">
                <i class="fas fa-list me-2 text-primary"></i>
                Department Projects
            </h5>
            <a href="{{ route('projects.create') }}" class="btn-modern btn-modern-sm btn-modern-primary">
                <i class="fas fa-plus"></i>
                <span>New Project</span>
            </a>
        </div>
        <div class="card-modern-body p-0">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Assigned Employees</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($department->projects as $project)
                        <tr style="cursor: pointer;" onclick="window.location.href='{{ route('projects.show', $project) }}'">
                            <td>
                                <div class="fw-semibold">{{ $project->name }}</div>
                                <small class="text-muted">{{ Str::limit($project->description ?? 'No description', 40) }}</small>
                            </td>
                            <td>
                                <span class="badge-modern badge-modern-{{ $project->status_color ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $project->start_date->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $project->end_date->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <span class="badge-modern badge-modern-info">
                                    {{ $project->departmentUsers()->wherePivot('department_id', $department->id)->count() }} members
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" onclick="event.stopPropagation();">
                                    <a href="{{ route('projects.show', $project) }}" class="btn-modern btn-modern-sm btn-modern-secondary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('projects.edit', $project) }}" class="btn-modern btn-modern-sm btn-modern-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-project-diagram fa-2x mb-2 d-block"></i>
                                No projects found for your department
                                <div class="mt-2">
                                    <a href="{{ route('projects.create') }}" class="btn-modern btn-modern-primary btn-modern-sm">
                                        <i class="fas fa-plus"></i>
                                        <span>Create First Project</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Project Modal -->
    <div class="modal fade" id="createProjectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('projects.store') }}" method="POST" id="createProjectForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="project_name" class="form-label">Project Name</label>
                                    <input type="text" class="form-control" id="project_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="project_status" class="form-label">Status</label>
                                    <select class="form-select" id="project_status" name="status" required>
                                        <option value="planning">Planning</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="on_hold">On Hold</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="project_description" class="form-label">Description</label>
                            <textarea class="form-control" id="project_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="project_priority" class="form-label">Priority</label>
                            <select class="form-select" id="project_priority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assign to Department</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="department_ids[]" value="{{ $department->id }}" id="dept_{{ $department->id }}" checked>
                                <label class="form-check-label" for="dept_{{ $department->id }}">
                                    {{ $department->name }}
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Employees Modal -->
    <div class="modal fade" id="viewEmployeesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assigned Employees</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="assignedEmployeesList">
                        <!-- Employees will be loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modals properly
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        // Ensure modal has proper Bootstrap initialization
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            new bootstrap.Modal(modal, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
        }
    });
    // Create Project Form
    const createProjectForm = document.getElementById('createProjectForm');
    if (createProjectForm) {
        createProjectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Creating...';
            submitBtn.disabled = true;
            
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
                    alert('Project created successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to create project'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating project. Please try again.');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // View Employees Modal
    const viewEmployeesButton = document.querySelector('#viewEmployeesModal');
    const assignedEmployeesList = document.querySelector('#assignedEmployeesList');

    if (viewEmployeesButton) {
        viewEmployeesButton.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const projectId = button.getAttribute('data-project-id');
            
            fetch(`/projects/${projectId}/assigned-employees`)
                .then(response => response.json())
                .then(data => {
                    if (data.employees && data.employees.length > 0) {
                        assignedEmployeesList.innerHTML = `
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.employees.map(employee => `
                                        <tr>
                                            <td>${employee.name}</td>
                                            <td>${employee.role}</td>
                                            <td>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="removeEmployee(${projectId}, ${employee.id})">
                                                    Remove
                                                </button>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        `;
                    } else {
                        assignedEmployeesList.innerHTML = '<p class="text-muted">No employees assigned to this project.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading employees:', error);
                    assignedEmployeesList.innerHTML = '<p class="text-danger">Error loading employees.</p>';
                });
        });
    }

    function removeEmployee(projectId, userId) {
        if (confirm('Are you sure you want to remove this employee from the project?')) {
            fetch(`/projects/${projectId}/employees/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error removing employee: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error removing employee. Please try again.');
            });
        }
    }
});
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'Project Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2 fw-bold text-gradient">
                <i class="fas fa-project-diagram me-2"></i>
                Project Management
            </h1>
            <p class="text-muted mb-0">Manage and track all your projects</p>
        </div>
        @if(in_array(auth()->user()->role, ['super-admin', 'hr-admin']))
            <a href="{{ route('projects.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i>
                <span>Add New Project</span>
            </a>
        @endif
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Projects Grid -->
    <div class="row g-4">
        @forelse($projects as $project)
            <div class="col-lg-4 col-md-6">
                <div class="card-modern h-100" style="cursor: pointer;" onclick="window.location.href='{{ route('projects.show', $project) }}'">
                    <div class="card-modern-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-modern-title mb-0">{{ $project->name }}</h5>
                            <span class="badge-modern badge-modern-{{ $project->status === 'completed' ? 'success' : ($project->status === 'in_progress' ? 'warning' : ($project->status === 'planning' ? 'info' : 'secondary')) }}">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-modern-body">
                        <p class="text-muted mb-3">{{ $project->description ?? 'No description available' }}</p>

                        <div class="mb-3">
                            <small class="text-muted">Timeline:</small><br>
                            <strong>{{ \Carbon\Carbon::parse($project->start_date)->format('M d, Y') }}</strong> -
                            <strong>{{ \Carbon\Carbon::parse($project->end_date)->format('M d, Y') }}</strong>
                        </div>

                        @if($project->tasks->count() > 0)
                            <div class="mb-3">
                                <small class="text-muted">Tasks Progress:</small>
                                <div class="progress mt-1" style="height: 8px;">
                                    @php
                                        $totalProgress = $project->tasks->avg('progress') ?? 0;
                                    @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $totalProgress }}%"></div>
                                </div>
                                <small class="text-muted">{{ round($totalProgress) }}% Complete</small>
                            </div>
                        @endif

                        <div class="mb-3">
                            <small class="text-muted">Assigned Users:</small><br>
                            @if($project->departmentUsers()->count() > 0)
                                @foreach($project->departmentUsers()->take(3)->get() as $user)
                                    <span class="badge bg-primary me-1">{{ $user->name }}</span>
                                @endforeach
                                @if($project->departmentUsers()->count() > 3)
                                    <span class="badge bg-secondary">+{{ $project->departmentUsers()->count() - 3 }} more</span>
                                @endif
                            @else
                                <span class="text-muted">No users assigned</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-modern-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Created {{ $project->created_at->diffForHumans() }}</small>
                            <div class="btn-group" role="group" onclick="event.stopPropagation();">
                                <a href="{{ route('projects.show', $project) }}" class="btn-modern btn-modern-sm btn-modern-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array(auth()->user()->role, ['super-admin', 'hr-admin', 'department-admin']))
                                    <button type="button" class="btn-modern btn-modern-sm btn-modern-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editProjectModal{{ $project->id }}"
                                            title="Edit Project">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn-modern btn-modern-sm btn-modern-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#assignUsersModal{{ $project->id }}"
                                            title="Assign Users">
                                        <i class="fas fa-users"></i>
                                    </button>
                                    <button type="button" class="btn-modern btn-modern-sm btn-modern-danger"
                                            onclick="confirmDelete({{ $project->id }}, '{{ $project->name }}')"
                                            title="Delete Project">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card-modern text-center py-5">
                    <div class="card-modern-body">
                        <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted mb-2">No Projects Found</h5>
                        <p class="text-muted mb-4">Get started by creating your first project.</p>
                        @if(in_array(auth()->user()->role, ['super-admin', 'hr-admin']))
                            <a href="{{ route('projects.create') }}" class="btn-modern btn-modern-primary">
                                <i class="fas fa-plus"></i>
                                <span>Create Project</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>



    <!-- Delete Project Form -->
    <form id="deleteProjectForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Edit Project Modals -->
    @foreach($projects as $project)
        <div class="modal fade" id="editProjectModal{{ $project->id }}" tabindex="-1" aria-labelledby="editProjectModalLabel{{ $project->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProjectModalLabel{{ $project->id }}">Edit Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('projects.update', $project) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="name{{ $project->id }}" class="form-label">Project Name</label>
                                <input type="text" class="form-control" id="name{{ $project->id }}" name="name" value="{{ $project->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="description{{ $project->id }}" class="form-label">Description</label>
                                <textarea class="form-control" id="description{{ $project->id }}" name="description" rows="3">{{ $project->description }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date{{ $project->id }}" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date{{ $project->id }}" name="start_date" value="{{ $project->start_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="end_date{{ $project->id }}" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date{{ $project->id }}" name="end_date" value="{{ $project->end_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status{{ $project->id }}" class="form-label">Status</label>
                                        <select class="form-select" id="status{{ $project->id }}" name="status" required>
                                            <option value="planning" {{ $project->status === 'planning' ? 'selected' : '' }}>Planning</option>
                                            <option value="in-progress" {{ $project->status === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="on-hold" {{ $project->status === 'on-hold' ? 'selected' : '' }}>On Hold</option>
                                            <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="priority{{ $project->id }}" class="form-label">Priority</label>
                                        <select class="form-select" id="priority{{ $project->id }}" name="priority">
                                            <option value="low" {{ $project->priority === 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="medium" {{ $project->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="high" {{ $project->priority === 'high' ? 'selected' : '' }}>High</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Project</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Assign Users Modal -->
        <div class="modal fade" id="assignUsersModal{{ $project->id }}" tabindex="-1" aria-labelledby="assignUsersModalLabel{{ $project->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignUsersModalLabel{{ $project->id }}">Assign Users to {{ $project->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Use the department management section to assign users to this project.</p>
                        <div class="text-center">
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-primary">
                                <i class="fas fa-users me-2"></i>Manage Project Users
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
<script>
    function confirmDelete(projectId, projectName) {
        if (confirm(`Are you sure you want to delete project "${projectName}"? This action cannot be undone.`)) {
            const form = document.getElementById('deleteProjectForm');
            form.action = `/projects/${projectId}`;
            form.submit();
        }
    }

    // Initialize Bootstrap modals with proper configuration
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all modals with proper backdrop configuration
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            // Set default backdrop configuration
            modal.setAttribute('data-bs-backdrop', 'true');
            modal.setAttribute('data-bs-keyboard', 'true');
            
            modal.addEventListener('shown.bs.modal', function() {
                const firstInput = this.querySelector('input, select, textarea');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        });

        // Fix modal backdrop issue by ensuring Bootstrap is properly initialized
        if (typeof bootstrap !== 'undefined') {
            // Re-initialize all modals
            modals.forEach(modal => {
                new bootstrap.Modal(modal, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
            });
        }
    });

    // Handle modal button clicks with proper event handling
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-bs-toggle="modal"]')) {
            e.preventDefault();
            const targetModal = document.querySelector(e.target.getAttribute('data-bs-target'));
            if (targetModal && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(targetModal, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
                modal.show();
            }
        }
    });
</script>
@endsection

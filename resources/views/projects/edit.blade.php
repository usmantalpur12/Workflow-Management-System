@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
<div class="container-fluid mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-4 fw-bold mb-0">
            <i class="fas fa-edit me-2"></i>Edit Project
        </h1>
        <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Project
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Project Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('projects.update', $project) }}" method="POST" id="editProjectForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Project Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ $project->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="planning" {{ $project->status === 'planning' ? 'selected' : '' }}>Planning</option>
                                        <option value="in_progress" {{ $project->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="on_hold" {{ $project->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                        <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4">{{ $project->description }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select" id="priority" name="priority" required>
                                        <option value="low" {{ $project->priority === 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ $project->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ $project->priority === 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $project->start_date->format('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $project->end_date->format('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Project</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Project Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-value text-primary">{{ $project->tasks->count() }}</div>
                                <div class="stat-label">Total Tasks</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-value text-success">{{ $project->tasks->where('status', 'completed')->count() }}</div>
                                <div class="stat-label">Completed</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-value text-info">{{ $project->departments->count() }}</div>
                                <div class="stat-label">Departments</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-value text-warning">{{ $project->departmentUsers->count() }}</div>
                                <div class="stat-label">Assigned Users</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Assigned Departments</h5>
                </div>
                <div class="card-body">
                    @if($project->departments->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($project->departments as $department)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $department->name }}
                                    <span class="badge bg-primary rounded-pill">{{ $department->users->count() }} users</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No departments assigned</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editProjectForm');
    
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Updating...';
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
                    alert('Project updated successfully!');
                    window.location.href = "{{ route('projects.show', $project) }}";
                } else {
                    alert('Error: ' + (data.message || 'Failed to update project'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating project. Please try again.');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.stat-item {
    padding: 1rem 0;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
@endpush
@endsection

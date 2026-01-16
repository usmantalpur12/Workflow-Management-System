@extends('layouts.app')

@section('content')
<div class="cati-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Assign Departments to {{ $project->name }}</h1>
        <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <form action="{{ route('projects.update-departments', $project) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Current Departments -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Current Departments</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @forelse($project->departments as $department)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-building text-info me-2"></i>
                                        {{ $department->name }}
                                        <span class="badge bg-info ms-2">{{ $project->usersByDepartment($department)->count() }} Members</span>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="department_ids[]" value="{{ $department->id }}" checked>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-muted">No departments assigned yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Departments -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Available Departments</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @forelse($departments as $department)
                                @if(!$project->departments->contains($department))
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-building text-secondary me-2"></i>
                                            {{ $department->name }}
                                            <span class="badge bg-secondary ms-2">{{ $department->users()->count() }} Members</span>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="department_ids[]" value="{{ $department->id }}">
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="list-group-item text-muted">No departments available</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
        </div>
    </form>
</div>
@endsection

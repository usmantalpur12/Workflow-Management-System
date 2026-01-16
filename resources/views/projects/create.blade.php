@extends('layouts.app')

@section('title', 'Add New Project')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2 fw-bold text-gradient">
                <i class="fas fa-plus-circle me-2"></i>
                Add New Project
            </h1>
            <p class="text-muted mb-0">Create a new project and assign it to departments</p>
        </div>
        <a href="{{ route('projects.index') }}" class="btn-modern btn-modern-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Projects</span>
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-modern-header">
                    <h5 class="card-modern-title mb-0">
                        <i class="fas fa-plus me-2 text-primary"></i>
                        Create New Project
                    </h5>
                </div>
                <div class="card-modern-body">
                    <form action="{{ route('projects.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-modern-group">
                                    <label for="name" class="form-modern-label">Project Name</label>
                                    <input type="text" class="form-modern-input @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required placeholder="Enter project name">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-modern-group">
                                    <label for="status" class="form-modern-label">Status</label>
                                    <select class="form-modern-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="planning" {{ old('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                                        <option value="in_progress" {{ old('status') == 'in_progress' || old('status') == 'in-progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="on_hold" {{ old('status') == 'on_hold' || old('status') == 'on-hold' ? 'selected' : '' }}>On Hold</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-modern-group">
                                    <label for="priority" class="form-modern-label">Priority</label>
                                    <select class="form-modern-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority') == 'medium' || old('priority') == '' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-modern-group">
                                    <label for="start_date" class="form-modern-label">Start Date</label>
                                    <input type="date" class="form-modern-input @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-modern-group">
                                    <label for="end_date" class="form-modern-label">End Date</label>
                                    <input type="date" class="form-modern-input @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        @if(auth()->user()->role === 'super-admin')
                            <div class="form-modern-group">
                                <label class="form-modern-label">Assign Departments</label>
                                <div class="row g-3">
                                    @foreach($departments as $department)
                                        <div class="col-md-6">
                                            <div class="form-check p-3 rounded-modern" style="border: 1px solid var(--border-color); background: var(--bg-secondary);">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="department_ids[]" 
                                                       value="{{ $department->id }}" 
                                                       id="department_{{ $department->id }}">
                                                <label class="form-check-label w-100" for="department_{{ $department->id }}" style="cursor: pointer;">
                                                    <i class="fas fa-building me-2 text-primary"></i>
                                                    <strong>{{ $department->name }}</strong>
                                                    <span class="badge-modern badge-modern-info ms-2">{{ $department->users()->count() }} Members</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @elseif(auth()->user()->role === 'hr-admin' && $departments->isNotEmpty())
                            <div class="form-modern-group">
                                <label class="form-modern-label">Department</label>
                                <div class="p-3 rounded-modern" style="border: 1px solid var(--border-color); background: var(--bg-secondary);">
                                    <i class="fas fa-building me-2 text-primary"></i>
                                    <strong>{{ $departments->first()->name }}</strong>
                                    <span class="badge-modern badge-modern-info ms-2">{{ $departments->first()->users()->count() }} Members</span>
                                    <p class="text-muted small mb-0 mt-2">Department is automatically linked on save</p>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('projects.index') }}" class="btn-modern btn-modern-secondary">
                                <i class="fas fa-times"></i>
                                <span>Cancel</span>
                            </a>
                            <button type="submit" class="btn-modern btn-modern-primary">
                                <i class="fas fa-save"></i>
                                <span>Create Project</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
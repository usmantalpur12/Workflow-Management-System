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
</div>
@endsection

@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2 fw-bold text-gradient">Dashboard</h1>
            <p class="text-muted mb-0">Welcome back, <strong>{{ Auth::user()->name }}</strong>!</p>
        </div>
        <div class="d-flex gap-2">
            @if(in_array(auth()->user()->role, ['super-admin', 'hr-admin']))
                <a href="{{ route('projects.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i>
                    <span>New Project</span>
                </a>
            @else
                <a href="{{ route('projects.index') }}" class="btn-modern btn-modern-secondary">
                    <i class="fas fa-project-diagram"></i>
                    <span>View Projects</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="stat-card-value">{{ $totalProjects }}</div>
                <div class="stat-card-label">Total Projects</div>
                <div class="stat-card-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>Active</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon" style="background: var(--success-light); color: var(--success);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card-value">{{ $totalUsers }}</div>
                <div class="stat-card-label">Total Users</div>
                <div class="stat-card-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>Registered</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon" style="background: var(--warning-light); color: var(--warning);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-value">{{ $completedProjects }}</div>
                <div class="stat-card-label">Completed Projects</div>
                <div class="stat-card-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>Done</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon" style="background: var(--danger-light); color: var(--danger);">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-card-value">{{ $overdueProjects }}</div>
                <div class="stat-card-label">Overdue Projects</div>
                <div class="stat-card-change negative">
                    <i class="fas fa-arrow-down"></i>
                    <span>Needs Attention</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Recent Activities -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-modern-header">
                    <h5 class="card-modern-title mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>
                        Recent Activities
                    </h5>
                    <button class="btn-modern btn-modern-sm btn-modern-secondary">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                </div>
                <div class="card-modern-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities as $activity)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $activity->project->name }}</div>
                                        <small class="text-muted">{{ Str::limit($activity->project->description ?? 'No description', 50) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge-modern badge-modern-info">
                                            <i class="fas fa-building me-1"></i>
                                            {{ $activity->department->name ?? 'No Department' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-modern badge-modern-{{ $activity->status_color ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $activity->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $activity->created_at->format('M d, Y') }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No recent activities
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card-modern">
                <div class="card-modern-header">
                    <h5 class="card-modern-title mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-modern-body">
                    <div class="d-flex flex-column gap-2">
                        @if(in_array(auth()->user()->role, ['super-admin', 'hr-admin']))
                            <a href="{{ route('projects.create') }}" class="btn-modern btn-modern-primary w-100">
                                <i class="fas fa-plus-circle"></i>
                                <span>Create New Project</span>
                            </a>
                        @endif
                        @if(auth()->user()->role === 'super-admin')
                            <a href="{{ route('users.index') }}" class="btn-modern btn-modern-secondary w-100">
                                <i class="fas fa-users"></i>
                                <span>Manage Users</span>
                            </a>
                            <a href="{{ route('departments.index') }}" class="btn-modern btn-modern-secondary w-100">
                                <i class="fas fa-building"></i>
                                <span>Manage Departments</span>
                            </a>
                        @endif
                        <a href="{{ route('projects.index') }}" class="btn-modern btn-modern-secondary w-100">
                            <i class="fas fa-tasks"></i>
                            <span>View All Projects</span>
                        </a>
                        <a href="{{ route('attendances.index') }}" class="btn-modern btn-modern-secondary w-100">
                            <i class="fas fa-clock"></i>
                            <span>View Attendance</span>
                        </a>
                        <a href="{{ route('profile.show') }}" class="btn-modern btn-modern-secondary w-100">
                            <i class="fas fa-user-cog"></i>
                            <span>Edit Profile</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card-modern mt-4">
                <div class="card-modern-header">
                    <h5 class="card-modern-title mb-0">
                        <i class="fas fa-chart-pie me-2 text-info"></i>
                        Quick Stats
                    </h5>
                </div>
                <div class="card-modern-body">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Active Projects</span>
                            <span class="fw-bold">{{ $totalProjects - $completedProjects }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Completion Rate</span>
                            <span class="fw-bold">
                                {{ $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100) : 0 }}%
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Overdue Rate</span>
                            <span class="fw-bold text-danger">
                                {{ $totalProjects > 0 ? round(($overdueProjects / $totalProjects) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

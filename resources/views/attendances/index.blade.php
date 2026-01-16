@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Attendance Management</h1>
        <div>
            @if(in_array(auth()->user()->role, ['hr-admin', 'department-admin', 'super-admin']))
                <a href="{{ route('attendances.export-excel') }}" class="btn btn-success me-2">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <a href="{{ route('attendances.export-pdf') }}" class="btn btn-danger me-2">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            @endif
        </div>
    </div>

    <!-- Clock In/Out Buttons -->
    @if(auth()->user()->role !== 'super-admin')
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Clock In/Out</h5>
                        <div class="d-flex gap-2">
                            <form action="{{ route('attendances.clock-in') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="gps_location" id="gps_location">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-clock"></i> Clock In
                                </button>
    </form>
                            <form action="{{ route('attendances.clock-out') }}" method="POST" class="d-inline">
        @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-clock"></i> Clock Out
                                </button>
    </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Attendance Table -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
                @if(auth()->user()->role === 'super-admin')
                    All Users Attendance Records
                @elseif(in_array(auth()->user()->role, ['hr-admin', 'department-admin']))
                    All Users Attendance Records
                @else
                    My Attendance Records
                @endif
            </h5>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                <th>User</th>
                            <th>Role</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                            <th>Duration</th>
                            <th>GPS Location</th>
                <th>Status</th>
                            @if(in_array(auth()->user()->role, ['hr-admin', 'department-admin', 'super-admin']))
                <th>Actions</th>
                            @endif
            </tr>
        </thead>
        <tbody>
                        @forelse($attendances as $attendance)
                <tr>
                                <td>{{ $attendance->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $attendance->user->profile_picture_url }}" 
                                             alt="Profile" 
                                             class="rounded-circle me-2" 
                                             style="width: 30px; height: 30px; object-fit: cover;">
                                        {{ $attendance->user->name }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $attendance->user->role === 'super-admin' ? 'danger' : ($attendance->user->role === 'hr-admin' ? 'warning' : ($attendance->user->role === 'department-admin' ? 'info' : 'secondary')) }}">
                                        {{ ucfirst(str_replace('-', ' ', $attendance->user->role)) }}
                                    </span>
                                </td>
                                <td>{{ $attendance->clock_in ? $attendance->clock_in->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                <td>{{ $attendance->clock_out ? $attendance->clock_out->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                <td>
                                    @if($attendance->clock_in && $attendance->clock_out)
                                        @php
                                            $duration = $attendance->clock_in->diff($attendance->clock_out);
                                            $hours = $duration->h + ($duration->days * 24);
                                            $minutes = $duration->i;
                                        @endphp
                                        {{ $hours }}h {{ $minutes }}m
                                    @elseif($attendance->clock_in)
                                        @php
                                            $duration = $attendance->clock_in->diff(now());
                                            $hours = $duration->h + ($duration->days * 24);
                                            $minutes = $duration->i;
                                        @endphp
                                        <span class="text-warning">{{ $hours }}h {{ $minutes }}m (Active)</span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $attendance->gps_location ?? 'N/A' }}</td>
                    <td>
                                    @if($attendance->clock_out)
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-warning">Active</span>
                                    @endif
                                </td>
                                @if(in_array(auth()->user()->role, ['hr-admin', 'department-admin', 'super-admin']))
                                    <td>
                                        @if(!$attendance->locked)
                                            <form action="{{ route('attendances.lock', $attendance) }}" method="POST" class="d-inline">
                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-lock"></i> Lock
                                                </button>
                            </form>
                                        @else
                                            <span class="badge bg-secondary">Locked</span>
                        @endif
                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ in_array(auth()->user()->role, ['hr-admin', 'department-admin', 'super-admin']) ? 9 : 8 }}" class="text-center">No attendance records found.</td>
                </tr>
                        @endforelse
        </tbody>
    </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Get GPS location for clock in
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('gps_location').value = 
                position.coords.latitude + ',' + position.coords.longitude;
        }, function(error) {
            console.log('GPS location not available');
        });
    }
</script>
@endsection
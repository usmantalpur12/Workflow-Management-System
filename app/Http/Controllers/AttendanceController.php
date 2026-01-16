<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AttendanceExport;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        
        // If user is super-admin, show all attendances
        if ($user->role === 'super-admin') {
            $attendances = Attendance::with('user')->orderBy('date', 'desc')->get();
        }
        // If user is admin, show all attendances
        elseif (in_array($user->role, ['hr-admin', 'department-admin'])) {
            $attendances = Attendance::with('user')->orderBy('date', 'desc')->get();
        } else {
            // If regular user, show only their attendances
            $attendances = Attendance::where('user_id', $user->id)->with('user')->orderBy('date', 'desc')->get();
        }
        
        return view('attendances.index', compact('attendances'));
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is already clocked in
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->whereNull('clock_out')
            ->first();
            
        if ($existingAttendance) {
            return redirect()->back()->with('error', 'You are already clocked in');
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
        ]);
        
        // Send notification to super-admin
        $this->notifySuperadmin($user, 'clock_in');
        
        return redirect()->back()->with('success', 'Clocked in successfully');
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->latest()
            ->first();
            
        if ($attendance) {
            $attendance->update(['clock_out' => now()]);
            
            // Send notification to super-admin
            $this->notifySuperadmin($user, 'clock_out');
            
            return redirect()->back()->with('success', 'Clocked out successfully');
        }
        
        return redirect()->back()->with('error', 'No active clock-in found');
    }

    private function notifySuperadmin($user, $action)
    {
        // Get all super-admin users
        $superadmins = User::where('role', 'super-admin')->get();
        
        foreach ($superadmins as $superadmin) {
            // Store notification in session for now (you can implement database notifications later)
            $message = $user->name . ' has ' . ($action === 'clock_in' ? 'clocked in' : 'clocked out') . ' at ' . now()->format('H:i:s');
            
            // You can implement real-time notifications here using Pusher or WebSockets
            // For now, we'll store in session
            session()->flash('superadmin_notification', $message);
        }
    }

    public function exportExcel()
    {
        // Check if user is admin or super-admin
        if (!in_array(Auth::user()->role, ['hr-admin', 'department-admin', 'super-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        return Excel::download(new AttendanceExport, 'attendance.xlsx');
    }

    public function exportPdf()
    {
        // Check if user is admin or super-admin
        if (!in_array(Auth::user()->role, ['hr-admin', 'department-admin', 'super-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $attendances = Attendance::with('user')->get();
        $pdf = Pdf::loadView('attendances.pdf', compact('attendances'));
        return $pdf->download('attendance.pdf');
    }

    public function lock(Request $request, Attendance $attendance)
    {
        // Check if user is admin or super-admin
        if (!in_array(Auth::user()->role, ['hr-admin', 'department-admin', 'super-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $attendance->update(['locked' => true]);
        return redirect()->back()->with('success', 'Attendance locked');
    }

    public function getNotifications()
    {
        // Check if user is admin, super-admin, or hr-admin
        if (!in_array(Auth::user()->role, ['super-admin', 'hr-admin', 'department-admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get recent attendance activities
        $recentActivities = Attendance::with('user')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->orWhere('updated_at', '>=', now()->subMinutes(5))
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($attendance) {
                $action = $attendance->clock_out ? 'clocked out' : 'clocked in';
                return [
                    'user' => $attendance->user->name,
                    'action' => $action,
                    'time' => $attendance->updated_at->format('H:i:s'),
                    'date' => $attendance->updated_at->format('Y-m-d')
                ];
            });

        return response()->json($recentActivities);
    }
}
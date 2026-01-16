<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Attendance::with('user')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'User Name',
            'Clock In',
            'Clock Out',
            'GPS Location',
            'Locked',
            'Created At',
            'Updated At'
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->id,
            $attendance->user ? $attendance->user->name : 'N/A',
            $attendance->clock_in ? $attendance->clock_in->format('Y-m-d H:i:s') : 'N/A',
            $attendance->clock_out ? $attendance->clock_out->format('Y-m-d H:i:s') : 'N/A',
            $attendance->gps_location ?? 'N/A',
            $attendance->locked ? 'Yes' : 'No',
            $attendance->created_at->format('Y-m-d H:i:s'),
            $attendance->updated_at->format('Y-m-d H:i:s')
        ];
    }
} 
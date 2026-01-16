<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskClockLog extends Model
{
    protected $fillable = [
        'task_id', 'user_id', 'clock_in', 'clock_out',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute()
    {
        if (!$this->clock_out) {
            return now()->diffInSeconds($this->clock_in);
        }
        return $this->clock_out->diffInSeconds($this->clock_in);
    }

    public function getDurationFormattedAttribute()
    {
        $duration = $this->duration;
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}

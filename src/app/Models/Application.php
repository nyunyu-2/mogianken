<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'clock_in_time',
        'clock_out_time',
        'reason',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function getIsPendingAttribute()
    {
        return $this->status === '承認待ち';
    }

    public function breakTimes()
    {
        return $this->hasMany(ApplicationBreakTime::class);
    }

    public function application_break_times()
    {
        return $this->hasMany(ApplicationBreakTime::class);
    }

    public function getFormattedBreakDurationAttribute()
    {
        if ($this->breakTimes->isEmpty()) {
            return '-';
        }

        $totalSeconds = 0;
        foreach ($this->breakTimes as $break) {
            if ($break->break_in_time && $break->break_out_time) {
                $start = \Carbon\Carbon::parse($break->break_in_time);
                $end = \Carbon\Carbon::parse($break->break_out_time);
                $totalSeconds += $start->diffInSeconds($end);
            }
        }

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}

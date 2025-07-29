<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\BreakTime;
use App\Models\User;


class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'break_in_time',
        'break_out_time',
    ];

    //労働時間
    public function getWorkingHoursAttribute()
    {
        if ($this->clock_in_time && $this->clock_out_time) {
            $clockIn = Carbon::parse($this->clock_in_time);
            $clockOut = Carbon::parse($this->clock_out_time);

            $totalMinutes = $clockOut->diffInMinutes($clockIn);

            $breakMinutes = $this->breaks->sum(function ($break) {
                if ($break->break_in_time && $break->break_out_time) {
                    return Carbon::parse($break->break_out_time)
                        ->diffInMinutes(Carbon::parse($break->break_in_time));
                }
                return 0;
            });

            $workingMinutes = $totalMinutes - $breakMinutes;

            $hours = floor($workingMinutes / 60);
            $minutes = $workingMinutes % 60;

            return sprintf('%02d:%02d', $hours, $minutes);
        }

        return null;
    }

    //休憩時間
    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function getBreakDurationAttribute()
    {
        $totalMinutes = $this->breaks->sum(function ($break) {
            if ($break->break_in_time && $break->break_out_time) {
                return \Carbon\Carbon::parse($break->break_out_time)
                    ->diffInMinutes(\Carbon\Carbon::parse($break->break_in_time));
            }
            return 0;
        });

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }


    public function getFormattedClockInTimeAttribute()
    {
        return $this->clock_in_time ? \Carbon\Carbon::parse($this->clock_in_time)->format('H:i') : '-';
    }

    public function getFormattedClockOutTimeAttribute()
    {
        return $this->clock_out_time ? \Carbon\Carbon::parse($this->clock_out_time)->format('H:i') : '-';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function hasPendingApplication()
    {
        return $this->applications()->where('status', '承認待ち')->exists();
    }

    // Attendance.php
    public function applicationBreakTimes()
    {
        return $this->hasManyThrough(
            \App\Models\ApplicationBreakTime::class,
            \App\Models\Application::class,
            'attendance_id',     // Application側の外部キー
            'application_id',    // ApplicationBreakTime側の外部キー
            'id',                // Attendanceの主キー
            'id'                 // Applicationの主キー
        );
    }

}

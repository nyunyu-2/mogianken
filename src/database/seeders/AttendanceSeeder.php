<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('email', '!=', config('admin.email'))->get(); // 一般ユーザーのみ

        foreach ($users as $user) {

            if ($user->email === 'user@example.com') {
                continue;
            }

            for ($i = 0; $i < 5; $i++) {
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => Carbon::now()->subDays($i)->format('Y-m-d'),
                    'clock_in_time' => '09:00:00',
                    'clock_out_time' => '18:00:00',
                ]);

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_in_time' => '12:00:00',
                    'break_out_time' => '13:00:00',
                ]);

            }
        }
    }
}

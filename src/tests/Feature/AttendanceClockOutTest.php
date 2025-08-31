<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 退勤ボタンを押すと退勤できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤済みの勤怠を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in_time' => now()->subHours(8)->format('H:i'),
            'clock_out_time' => null,
        ]);

        $response = $this->post('/attendance/clock-out');

        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_out_time' => now()->format('H:i:s'),
        ]);
    }

    /** @test */
    public function 勤怠一覧で退勤時刻が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in_time' => now()->subHours(8)->format('H:i'),
            'clock_out_time' => now()->format('H:i'),
        ]);

        $response = $this->get('/attendance/list');

        $attendanceDate = Carbon::parse($attendance->date)->locale('ja')->isoFormat('MM/DD(ddd)');
        $attendanceClockOut = Carbon::parse($attendance->clock_out_time)->format('H:i');

        $response->assertSee($attendanceDate);
        $response->assertSee($attendanceClockOut);
    }
}



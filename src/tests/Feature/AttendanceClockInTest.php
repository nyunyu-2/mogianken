<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤ボタンを押すと出勤できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/attendance/clock-in');
        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => today(),
        ]);
    }

    /** @test */
    public function 出勤は一日一回だけ可能()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/clock-in');

        $response = $this->post('/attendance/clock-in');

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertEquals(1, Attendance::where('user_id', $user->id)->count());
    }

    /** @test */
    public function 勤怠一覧で出勤時刻が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in_time' => now()->format('H:i'),
            'clock_out_time' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $attendanceDate = \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('MM/DD(ddd)');
        $attendanceClockIn = \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i');

        $response->assertSee($attendanceDate);
        $response->assertSee($attendanceClockIn);
    }
}


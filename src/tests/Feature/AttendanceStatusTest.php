<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\ApplicationBreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤務外の場合は勤務外と表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合は出勤中と表示される()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in_time' => now(),
            'clock_out_time' => null,
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合は休憩中と表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_time' => now()->subHours(2),
            'clock_out_time' => null,
        ]);

        $attendance->breaks()->create([
            'break_in_time' => now()->subMinutes(30),
            'break_out_time' => null,
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合は退勤済と表示される()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in_time' => now()->subHours(8),
            'clock_out_time' => now(),
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }
}

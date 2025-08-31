<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面に名前が正しく表示される()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertSee('テストユーザー');
    }

    /** @test */
    public function 勤怠詳細画面に日付が正しく表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertSee(
            \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('YYYY年')
        );
        $response->assertSee(
            \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('M月D日')
        );
    }

    /** @test */
    public function 勤怠詳細画面に出勤・退勤時刻が正しく表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_time' => now()->format('H:i'),
            'clock_out_time' => now()->addHours(8)->format('H:i'),
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertSee($attendance->clock_in_time->format('H:i'));
        $response->assertSee($attendance->clock_out_time->format('H:i'));
    }

    /** @test */
    public function 勤怠詳細画面に休憩時刻が正しく表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $break = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in_time' => now()->addHours(2)->format('H:i'),
            'break_out_time' => now()->addHours(2)->addMinutes(30)->format('H:i'),
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertSee(Carbon::parse($break->break_in_time)->format('H:i'));
        $response->assertSee(Carbon::parse($break->break_out_time)->format('H:i'));
    }
}


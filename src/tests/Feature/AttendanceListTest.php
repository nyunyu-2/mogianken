<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分の勤怠情報だけが表示される()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in_time' => now()->format('H:i'),
            'clock_out_time' => now()->addHours(8)->format('H:i'),
        ]);

        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'date' => today(),
            'clock_in_time' => now()->format('H:i'),
            'clock_out_time' => now()->addHours(8)->format('H:i'),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee($myAttendance->date->locale('ja')->isoFormat('MM/DD(ddd)'));
    }


    /** @test */
    public function デフォルト表示は現在の月である()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $currentMonthLabel = now()->format('Y/m');
        $response->assertSee($currentMonthLabel);
    }

    /** @test */
    public function 前月ボタンを押すと前月の勤怠情報が表示される()
    {
        $user = User::factory()->create();

        $previousMonthDate = now()->subMonth()->firstOfMonth();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $previousMonthDate,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=' . $previousMonthDate->format('Y-m'));

        $response->assertSee($previousMonthDate->format('m/d'));
    }

    /** @test */
    public function 翌月ボタンを押すと翌月の勤怠情報が表示される()
    {
        $user = User::factory()->create();

        $nextMonthDate = now()->addMonth()->firstOfMonth();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonthDate,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=' . $nextMonthDate->format('Y-m'));

        $response->assertSee($nextMonthDate->format('m/d'));
    }

    /** @test */
    public function 詳細ボタンを押すとその日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee(route('user.attendance.show', ['id' => $attendance->id]));
    }
}


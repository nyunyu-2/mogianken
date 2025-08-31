<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // ユーザー作成
        $this->user = User::factory()->create();

        // 当日の出勤作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => today(),
            'clock_in_time' => '09:00:00',
        ]);
    }

    /** @test */
    public function 休憩ボタンが正しく機能する()
    {
        $response = $this->actingAs($this->user)
                            ->post(route('user.attendance.breakIn', $this->attendance->id));

        $response->assertRedirect(route('user.attendance.create'));
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $this->attendance->id,
            'break_out_time' => null,
        ]);
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        // 1回目休憩
        $this->actingAs($this->user)->post(route('user.attendance.breakIn', $this->attendance->id));
        $this->actingAs($this->user)->post(route('user.attendance.breakOut', $this->attendance->id));

        // 2回目休憩
        $this->actingAs($this->user)->post(route('user.attendance.breakIn', $this->attendance->id));

        $this->assertCount(2, BreakTime::where('attendance_id', $this->attendance->id)->get());
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $this->actingAs($this->user)->post(route('user.attendance.breakIn', $this->attendance->id));

        $latestBreak = BreakTime::where('attendance_id', $this->attendance->id)->latest()->first();

        $this->actingAs($this->user)->post(route('user.attendance.breakOut', $this->attendance->id));

        $latestBreak->refresh();
        $this->assertNotNull($latestBreak->break_out_time);
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        // 1回目
        $this->actingAs($this->user)->post(route('user.attendance.breakIn', $this->attendance->id));
        $this->actingAs($this->user)->post(route('user.attendance.breakOut', $this->attendance->id));

        // 2回目
        $this->actingAs($this->user)->post(route('user.attendance.breakIn', $this->attendance->id));
        $this->actingAs($this->user)->post(route('user.attendance.breakOut', $this->attendance->id));

        $breaks = BreakTime::where('attendance_id', $this->attendance->id)->get();
        $this->assertCount(2, $breaks);
        $this->assertNotNull($breaks[0]->break_out_time);
        $this->assertNotNull($breaks[1]->break_out_time);
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $this->actingAs($this->user)->post(route('user.attendance.breakIn', $this->attendance->id));
        $this->actingAs($this->user)->post(route('user.attendance.breakOut', $this->attendance->id));

        $response = $this->actingAs($this->user)->get(route('user.attendance.create'));

        $break = BreakTime::where('attendance_id', $this->attendance->id)->first();
        $response->assertSee(Carbon::parse($break->break_in_time)->format('H:i'));
        $response->assertSee(Carbon::parse($break->break_out_time)->format('H:i'));
    }
}



<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー作成
        $this->admin = User::factory()->create([
            'email' => config('admin.email'),
        ]);

        // 一般ユーザー作成
        $this->user = User::factory()->create();

        // 勤怠データ作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);
    }

    /** @test */
    public function 勤怠詳細画面に正しい情報が表示される()
    {
        $response = $this->actingAs($this->admin)
                         ->get(route('admin.attendance.show', $this->attendance->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee($this->user->name);
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラーになる()
    {
        $response = $this->actingAs($this->admin)
                         ->put(route('admin.attendance.update', $this->attendance->id), [
                             'clock_in_time' => '19:00',
                             'clock_out_time' => '18:00',
                         ]);

        $response->assertSessionHasErrors(['clock_in_time']);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラーになる()
    {
        $response = $this->actingAs($this->admin)
                         ->put(route('admin.attendance.update', $this->attendance->id), [
                             'clock_in_time' => '09:00',
                             'clock_out_time' => '18:00',
                             'breaks' => [
                                 ['start' => '19:00', 'end' => '19:30'],
                             ],
                         ]);

        $response->assertSessionHasErrors(['breaks.0.start']);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラーになる()
    {
        $response = $this->actingAs($this->admin)
                         ->put(route('admin.attendance.update', $this->attendance->id), [
                             'clock_in_time' => '09:00',
                             'clock_out_time' => '18:00',
                             'breaks' => [
                                 ['start' => '12:00', 'end' => '19:00'],
                             ],
                         ]);

        $response->assertSessionHasErrors(['breaks.0.end']);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーになる()
    {
        $response = $this->actingAs($this->admin)
                         ->put(route('admin.attendance.update', $this->attendance->id), [
                             'clock_in_time' => '09:00',
                             'clock_out_time' => '18:00',
                         ]);

        $response->assertSessionHasErrors(['reason']);
    }
}


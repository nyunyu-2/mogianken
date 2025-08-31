<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminUserInfoTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー
        $this->admin = User::factory()->create([
            'email' => config('admin.email'),
        ]);

        // 一般ユーザー
        $this->user = User::factory()->create([
            'name' => '一般太郎',
            'email' => 'user@example.com',
        ]);

        // 勤怠データ（今月と先月分を用意）
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->subMonth()->startOfMonth(),
            'clock_in_time' => '10:00:00',
            'clock_out_time' => '19:00:00',
        ]);
    }

    /** @test */
    public function 管理者は全ユーザーの氏名とメールアドレスを確認できる()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.staff.index'));

        $response->assertStatus(200);
        $response->assertSee('一般太郎');
        $response->assertSee('user@example.com');
    }

    /** @test */
    public function 管理者はユーザーの勤怠情報を確認できる()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.staff.attendance.show', $this->user->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 前月を押すと前月の勤怠情報が表示される()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.staff.attendance.show', [
                'id' => $this->user->id,
                'month' => now()->subMonth()->format('Y-m'),
            ]));

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 翌月を押すと翌月の勤怠情報が表示される()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.staff.attendance.show', [
                'id' => $this->user->id,
                'month' => now()->addMonth()->format('Y-m'),
            ]));

        $response->assertStatus(200);
        $response->assertSee('データがありません');
    }

    /** @test */
    public function 詳細ボタンを押すとその日の勤怠詳細に遷移できる()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.show', $this->attendance->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}

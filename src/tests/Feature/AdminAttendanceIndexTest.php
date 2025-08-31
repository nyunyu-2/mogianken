<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $users;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー作成
        $this->admin = User::factory()->create([
            'email' => config('admin.email'),
        ]);

        // 一般ユーザー複数作成
        $this->users = User::factory(3)->create();

        // 今日の日付
        $today = Carbon::today();

        // 勤怠データ作成（ユーザーごと）
        foreach ($this->users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $today,
                'clock_in_time' => '09:00:00',
                'clock_out_time' => '18:00:00',
            ]);
        }
    }

    /** @test */
    public function その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $response = $this->actingAs($this->admin)->get('/admin/attendances');

        foreach ($this->users as $user) {
            $response->assertSee($user->name);
        }

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 遷移した際に現在の日付が表示される()
    {
        $expectedDate = Carbon::today()->format('Y/m/d');
        $response = $this->actingAs($this->admin)->get('/admin/attendances');
        $response->assertSee($expectedDate);
    }

    /** @test */
    public function 前日を押下した時に前の日の勤怠情報が表示される()
    {
        $yesterday = Carbon::yesterday();

        Attendance::factory()->count($this->users->count())->sequence(
            fn ($sequence) => ['user_id' => $this->users[$sequence->index]->id]
        )->create([
            'date' => $yesterday,
            'clock_in_time' => '10:00:00',
            'clock_out_time' => '19:00:00',
        ]);

        $response = $this->actingAs($this->admin)
                        ->get('/admin/attendances?date=' . $yesterday->toDateString());
        $response->assertSee($yesterday->format('Y/m/d'));
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 翌日を押下した時に次の日の勤怠情報が表示される()
    {
        $tomorrow = Carbon::tomorrow();

        Attendance::factory()->count($this->users->count())->sequence(
            fn ($sequence) => ['user_id' => $this->users[$sequence->index]->id]
        )->create([
            'date' => $tomorrow,
            'clock_in_time' => '08:00:00',
            'clock_out_time' => '17:00:00',
        ]);

        $response = $this->actingAs($this->admin)
                        ->get('/admin/attendances?date=' . $tomorrow->toDateString());
        $response->assertSee($tomorrow->format('Y/m/d'));
        $response->assertSee('08:00');
        $response->assertSee('17:00');
    }

}

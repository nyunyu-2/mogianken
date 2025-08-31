<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Application;
use App\Models\Attendance;
use App\Models\ApplicationBreakTime;

class AdminApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー作成
        $this->admin = User::factory()->create([
            'email' => config('admin.email'),
        ]);
    }

    /** @test */
    public function 承認待ちの修正申請が全て表示されている()
    {
        $pendingApps = Application::factory()->count(3)->create(['status' => '承認待ち']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.application.index', ['status' => 'pending']));

        $response->assertStatus(200);
        foreach ($pendingApps as $app) {
            $response->assertSee($app->reason);
        }
    }

    /** @test */
    public function 承認済みの修正申請が全て表示されている()
    {
        $approvedApps = Application::factory()->count(2)->create(['status' => '承認済み']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.application.index', ['status' => 'approved']));

        $response->assertStatus(200);
        foreach ($approvedApps as $app) {
            $response->assertSee($app->reason);
        }
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示されている()
    {
        // 管理者ユーザー
        $admin = $this->admin;

        // 一般ユーザー
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $application = Application::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => '承認待ち',
            'reason' => '出勤時間の修正',
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        ApplicationBreakTime::factory()->create([
            'application_id' => $application->id,
            'break_in_time' => '12:00:00',
            'break_out_time' => '12:30:00',
        ]);



        $response = $this->actingAs($admin)
            ->get(route('admin.requests.edit', $application->id));

        $response->assertStatus(200);
        $response->assertSee('出勤時間の修正');
        $response->assertSee($user->name);
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $application = Application::factory()->create(['status' => '承認待ち']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.approval.approve'), [
                'application_id' => $application->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => '承認済み',
        ]);
    }
}

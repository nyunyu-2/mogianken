<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Application;


class AttendanceApplicationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('user.application.resubmit'), [
            'clock_in_time' => '18:00',
            'clock_out_time' => '09:00',
            'reason' => '修正理由',
        ]);

        $response->assertSessionHasErrors([
            'clock_in_time' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('user.application.resubmit'), [
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
            'reason' => '修正理由',
            'breaks' => [
                ['start' => '19:00', 'end' => null],
            ],
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('user.application.resubmit'), [
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
            'reason' => '修正理由',
            'breaks' => [
                ['start' => '12:00', 'end' => '19:00'],
            ],
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('user.application.resubmit'), [
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
            'reason' => '', // 未入力
        ]);

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }

    /** @test */
    public function 修正申請処理が実行される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(
            route('user.application.resubmit'), [
                'attendance_id' => $attendance->id,
                'clock_in_time' => '09:00',
                'clock_out_time' => '18:00',
                'reason' => '体調不良のため修正',
            ]
        );


        $response->assertRedirect(route('user.attendance.show', ['id' => $attendance->id]));

        $this->assertDatabaseHas('applications', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '承認待ち',
            'reason' => '体調不良のため修正',
        ]);
    }

    /** @test */
    public function 承認待ちにログインユーザーが行った申請が全て表示されていること()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pending = Application::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => '承認待ち',
        ]);

        $response = $this->get(route('user.application.index', ['tab' => 'pending']));

        foreach ($pending as $application) {
            $response->assertSee($application->reason);
        }
    }

    /** @test */
    public function 承認済みに管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $approved = Application::factory()->count(2)->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '承認済み',
        ]);


        $response = $this->get(route('user.application.index', ['tab' => 'approved']));

        foreach ($approved as $application) {
            $response->assertSee($application->reason);
        }
    }

    /** @test */
    public function 各申請の詳細を押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $application = Application::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '承認待ち',
            'reason' => '詳細確認テスト',
        ]);

        $response = $this->actingAs($user)->get(
            route('user.attendance.show', $attendance->id)
        );

        $response->assertStatus(200);
        $response->assertSee('詳細確認テスト');
    }
}

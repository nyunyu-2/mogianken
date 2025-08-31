<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Models\User;

class AttendanceDateTimeTest extends TestCase
{
    /** @test */
    public function 勤怠打刻画面に現在日時が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $weekMap = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $weekMap[now()->dayOfWeek];

        // 日付部分
        $expectedDate = now()->format("Y年n月j日（{$dayOfWeek}）");
        $response->assertSee($expectedDate);

        // 時間部分（秒なし）
        $expectedTime = now()->format("H:i");
        $response->assertSee($expectedTime);
    }
}


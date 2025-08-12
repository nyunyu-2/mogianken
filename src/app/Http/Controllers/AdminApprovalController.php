<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class AdminApprovalController extends Controller
{
    public function edit($id)
    {
        $application = Application::with(['user', 'attendance', 'application_break_times'])->findOrFail($id);

        return view('admin.approval.edit', compact('application'));
    }

    public function approve(Request $request)
    {
        $application = Application::with('application_break_times')->findOrFail($request->input('application_id'));

        // 承認ステータス変更
        $application->status = '承認済み';
        $application->save();

        // 関連する勤務情報を更新
        $attendance = $application->attendance;

        $attendance->clock_in_time = $application->clock_in_time;
        $attendance->clock_out_time = $application->clock_out_time;

        // 休憩時間の合計を計算
        $totalBreakMinutes = $application->application_break_times->sum(function ($break) {
            $start = \Carbon\Carbon::parse($break->break_in_time);
            $end = \Carbon\Carbon::parse($break->break_out_time);
            return $end->diffInMinutes($start);
        });

        // 勤務時間（分）を計算（出勤〜退勤 − 休憩時間）
        if ($attendance->clock_in_time && $attendance->clock_out_time) {
            $start = \Carbon\Carbon::parse($attendance->clock_in_time);
            $end = \Carbon\Carbon::parse($attendance->clock_out_time);
            $workDurationMinutes = $end->diffInMinutes($start) - $totalBreakMinutes;
        } else {
            $workDurationMinutes = null;
        }

        $attendance->break_duration = $totalBreakMinutes;
        $attendance->working_hours = $workDurationMinutes;

        $attendance->save();

        return redirect()->route('admin.requests.edit', ['id' => $application->id])
                        ->with('success', '申請を承認しました');
    }

}

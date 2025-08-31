<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\Application;
use App\Http\Requests\AttendanceRequest;


class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $prevDate = $currentDate->copy()->subDay();  // 前日
        $nextDate = $currentDate->copy()->addDay();  // 翌日

        // この日に該当する勤怠データを取得（必要に応じて調整）
        $attendances = Attendance::with(['user', 'applications.application_break_times'])
            ->whereDate('date', $currentDate)
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.index', [
            'currentDate' => $currentDate,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'attendances' => $attendances,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'applications.application_break_times'])->findOrFail($id);

        // 承認済み申請を取得（あれば）
        $approvedApplication = $attendance->applications()
            ->where('status', '承認済み')
            ->latest('updated_at')
            ->first();

        // 申請中のものも取得（あれば）
        $pendingApplication = $attendance->applications()
            ->where('status', '承認待ち')
            ->latest('updated_at')
            ->first();

        $application = $pendingApplication ?? $approvedApplication;

        $applicationBreaks = $application ? $application->application_break_times : collect();

        return view('admin.attendance.show', compact('attendance', 'application', 'applicationBreaks'));
    }

    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $application = $attendance->applications()->latest('updated_at')->first();
        if (!$application) {
            $application = new Application();
            $application->attendance_id = $attendance->id;
            $application->user_id = $attendance->user_id;
        }

        $application->clock_in_time = $request->input('clock_in_time');
        $application->clock_out_time = $request->input('clock_out_time');
        $application->reason = $request->input('reason');
        $application->status = '承認済み';

        $application->save();

        $attendance->clock_in_time = $application->clock_in_time;
        $attendance->clock_out_time = $application->clock_out_time;
        $attendance->save();

        $application->application_break_times()->delete();

        $breaks = $request->input('breaks', []);
        foreach ($breaks as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                $application->application_break_times()->create([
                    'break_in_time' => $break['start'],
                    'break_out_time' => $break['end'],
                ]);
            }
        }

        return redirect()->route('admin.attendance.show', $attendance->id)
                        ->with('success', '勤怠情報を修正し、申請済みに更新しました。');
    }

}

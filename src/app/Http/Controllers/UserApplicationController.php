<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceRequest;

class UserApplicationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $pendingApplications = Application::where('user_id', $user->id)
            ->where('status', '承認待ち')
            ->get();

        $approvedApplications = Application::where('user_id', $user->id)
            ->where('status', '承認済み')
            ->get();

        return view('user.application.index', compact('pendingApplications', 'approvedApplications'));
    }

    public function resubmit(AttendanceRequest $request)
    {
        $attendanceId = $request->input('attendance_id');
        $attendance = Attendance::findOrFail($attendanceId);

        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        // 承認待ちの申請
        $application = $attendance->applications()
            ->where('status', '承認待ち')
            ->first();

        if (!$application) {
            $application = new Application();
            $application->attendance_id = $attendance->id;
            $application->user_id = auth()->id();
        } else {
            $application->breakTimes()->delete();
        }

        $validated = $request->validated();

        $application->status = '承認待ち';
        $application->reason = $request->input('reason', '');
        $application->clock_in_time = $request->input('clock_in_time');
        $application->clock_out_time = $request->input('clock_out_time');
        $application->save();

        $breaks = $request->input('breaks', []);
        foreach ($breaks as $break) {
            if (!empty($break['start']) || !empty($break['end'])) {
                $application->breakTimes()->create([
                    'break_in_time' => $break['start'] ?? null,
                    'break_out_time' => $break['end'] ?? null,
                ]);
            }
        }

        return redirect()->route('user.attendance.show', ['id' => $attendance->id])
            ->with('success', '申請を承認待ちリストに登録しました。');

    }

    public function approve(Request $request)
    {
        $application = Application::with('application_break_times')->findOrFail($request->input('application_id'));

        // 申請を承認済みに
        $application->status = '承認済み';
        $application->save();

        // 勤務実績を更新
        $attendance = $application->attendance;
        $attendance->clock_in_time = $application->clock_in_time;
        $attendance->clock_out_time = $application->clock_out_time;

        // 申請に紐づく休憩時間の合計を計算
        $totalBreakMinutes = $application->application_break_times->sum(function ($break) {
            $start = \Carbon\Carbon::parse($break->break_in_time);
            $end = \Carbon\Carbon::parse($break->break_out_time);
            return $end->diffInMinutes($start);
        });

        $attendance->break_duration = $totalBreakMinutes;

        // 勤務時間計算
        $workDurationMinutes = null;
        if ($attendance->clock_in_time && $attendance->clock_out_time) {
            $start = \Carbon\Carbon::parse($attendance->clock_in_time);
            $end = \Carbon\Carbon::parse($attendance->clock_out_time);
            $workDurationMinutes = $end->diffInMinutes($start) - $totalBreakMinutes;
        }
        $attendance->working_hours = $workDurationMinutes;

        $attendance->save();

        return redirect()->route('admin.applications.index')
                        ->with('success', '申請を承認し、勤務情報を更新しました。');
    }
}

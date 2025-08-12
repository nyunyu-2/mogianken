<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class UserAttendanceController extends Controller
{
    public function create()
    {
        $now = now();

        $weekMap = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $weekMap[$now->dayOfWeek];

        $today = $now->format('Y年n月j日'). "（{$dayOfWeek}）";

        $currentTime = $now->format('H:i');

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->first();

        $latestBreak = null;
        if ($attendance) {
            $latestBreak = $attendance->breaks()->latest()->first();
        }

        // 状態判定
        $status = '勤務外';
        $canClockIn = true;
        $canClockOut = false;
        $canBreakIn = false;
        $canBreakOut = false;
        $isClockedOut = false;

        if ($attendance) {
            if ($attendance->clock_out_time) {
                $status = '退勤済';
                $isClockedOut = true;
            } elseif ($latestBreak && !$latestBreak->break_out_time) {
                $status = '休憩中';
                $canBreakOut = true;
                $canClockIn = false;
            } else {
                $status = '出勤中';
                $canClockIn = false;
                $canClockOut = true;
                $canBreakIn = true;
            }
        }

        return view('user.attendance.create', compact(
            'today', 'currentTime', 'status',
            'canClockIn', 'canClockOut', 'canBreakIn', 'canBreakOut', 'isClockedOut'
        ));
    }

    public function clockIn(Request $request)
    {
        $userId = Auth::id(); // ログイン中ユーザーのID
        $user = Auth::user(); // ユーザーのオブジェクト全部が取れる（name や email など）

        // 今日の日付
        $today = today();

        // すでに出勤済みか確認
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$existing) {
            // 出勤を記録
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'clock_in_time' => now()->format('H:i'),
            ]);
        }

        return redirect()->back()->with('message', '出勤しました');
    }

    public function clockOut(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->first();

        if (!$attendance) {
            return redirect()->back()->withErrors('出勤記録が見つかりません。');
        }

        if ($attendance->clock_out_time) {
            return redirect()->back()->withErrors('すでに退勤済みです。');
        }

        $attendance->clock_out_time = now();
        $attendance->save();

        return redirect()->route('user.attendance.create')->with('status', '退勤しました。');
    }

    public function breakIn(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->first();

        if (!$attendance) {
            return redirect()->back()->withErrors('出勤記録が見つかりません。');
        }

        // 最後の休憩が終了していないなら、新しい休憩を開始できない
        if ($attendance->breaks()->whereNull('break_out_time')->exists()) {
            return redirect()->back()->withErrors('前の休憩が終了していません。');
        }

        $attendance->breaks()->create([
            'break_in_time' => now()->format('Y-m-d H:i'),
        ]);

        return redirect()->route('user.attendance.create')->with('status', '休憩に入りました。');
    }

    public function breakOut(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->first();

        if (!$attendance) {
            return redirect()->back()->withErrors('出勤記録が見つかりません。');
        }

        $latestBreak = $attendance->breaks()->whereNull('break_out_time')->latest()->first();

        if (!$latestBreak) {
            return redirect()->back()->withErrors('現在休憩中ではありません。');
        }

        $latestBreak->update([
            'break_out_time' => now()->format('Y-m-d H:i'),
        ]);

        return redirect()->route('user.attendance.create')->with('status', '休憩から戻りました。');
    }

    // 勤務一覧
    public function index(Request $request)
    {
        $currentMonth = $request->input('month', now()->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $currentMonth);

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $attendances = Attendance::with(['breaks', 'applications.application_break_times'])
            ->where('user_id', Auth::id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($attendance) {
                // 最新の承認済み申請を取得
                $approvedApplication = $attendance->applications
                    ->where('status', '承認済み')
                    ->sortByDesc('updated_at')
                    ->first();

                if ($approvedApplication) {
                    $breaks = $approvedApplication->application_break_times;
                    $clockIn = $approvedApplication->clock_in_time;
                    $clockOut = $approvedApplication->clock_out_time;
                } else {
                    $breaks = $attendance->breaks;
                    $clockIn = $attendance->clock_in_time;
                    $clockOut = $attendance->clock_out_time;
                }

                // 合計休憩時間（分）
                $totalBreakMinutes = $breaks->sum(function ($break) {
                    $start = \Carbon\Carbon::parse($break->break_in_time ?? $break->start_time);
                    $end = \Carbon\Carbon::parse($break->break_out_time ?? $break->end_time);
                    return $end->diffInMinutes($start);
                });

                // 勤務時間（分）
                if ($clockIn && $clockOut) {
                    $start = \Carbon\Carbon::parse($clockIn);
                    $end = \Carbon\Carbon::parse($clockOut);
                    $workDurationMinutes = $end->diffInMinutes($start) - $totalBreakMinutes;
                } else {
                    $workDurationMinutes = null;
                }

                $attendance->formatted_break_duration = $totalBreakMinutes > 0
                    ? floor($totalBreakMinutes / 60) . '時間' . ($totalBreakMinutes % 60) . '分'
                    : '-';

                $attendance->working_hours = $workDurationMinutes !== null
                    ? floor($workDurationMinutes / 60) . '時間' . ($workDurationMinutes % 60) . '分'
                    : '-';

                return $attendance;
            });



        return view('user.attendance.index', [
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
            'prevMonth' => $date->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $date->copy()->addMonth()->format('Y-m'),
        ]);
    }

    // 勤務一覧詳細
    public function show($id)
    {
        $attendance = Attendance::findOrFail($id);

        $approvedApplication = $attendance->applications()
            ->where('status', '承認済み')
            ->with('application_break_times')
            ->latest('updated_at')
            ->first();

        $pendingApplication = $attendance->applications()
            ->where('status', '承認待ち')
            ->with('application_break_times')
            ->latest('updated_at')
            ->first();

        $application = $pendingApplication ?? $approvedApplication;

        $applicationBreaks = $application ? $application->application_break_times : collect();

        $totalBreakMinutes = $applicationBreaks->sum(function ($break) {
            $start = \Carbon\Carbon::parse($break->break_in_time);
            $end = \Carbon\Carbon::parse($break->break_out_time);
            return $end->diffInMinutes($start);
        });

        return view('user.attendance.show', compact(
            'attendance',
            'application',
            'applicationBreaks',
            'totalBreakMinutes',
        ));
    }
}

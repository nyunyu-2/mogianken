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

        $attendances = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();


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

        $application = $attendance->applications()
            ->where('status', '承認待ち')
            ->first();

        $applicationBreaks = $application ? $application->breakTimes : collect();


        return view('user.attendance.show', compact('attendance','application', 'applicationBreaks'));
    }
}

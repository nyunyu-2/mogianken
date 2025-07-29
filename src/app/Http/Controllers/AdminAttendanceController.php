<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;


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
        $attendances = Attendance::whereDate('date', $currentDate)->get();

        return view('admin.attendance.index', [
            'currentDate' => $currentDate,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'attendances' => $attendances,
        ]);
    }

}

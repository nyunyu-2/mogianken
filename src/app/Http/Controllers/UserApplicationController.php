<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

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

    public function resubmit(Request $request)
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
        }

        $application->status = '承認待ち';
        $application->reason = $request->input('reason', '');
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

        return redirect()->route('user.attendance.show', ['attendance' => $attendance->id])
            ->with('success', '申請を承認待ちリストに登録しました。');

    }
}

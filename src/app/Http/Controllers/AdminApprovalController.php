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
        $application = Application::findOrFail($request->input('application_id'));

        $application->is_pending = false;
        $application->save();

        return redirect()->route('admin.approval.index', ['status' => 'approved'])
                         ->with('success', '申請を承認しました');
    }
}

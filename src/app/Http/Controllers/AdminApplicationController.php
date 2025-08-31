<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class AdminApplicationController extends Controller
{
    public function index(Request $request)
    {
        $pendingApplications = Application::where('status', '承認待ち')->get();

        $approvedApplications = Application::where('status', '承認済み')->get();

        return view('admin.application.index', compact('pendingApplications','approvedApplications'));
    }
}

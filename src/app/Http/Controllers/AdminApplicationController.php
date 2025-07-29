<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class AdminApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::with(['user', 'attendance'])
            ->latest()
            ->get();

        return view('admin.application.index', compact('applications'));
    }
}

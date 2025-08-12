<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\UserApplicationController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminApprovalController;

use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [UserAttendanceController::class, 'create'])->name('user.attendance.create');
    Route::post('/attendance/clock-in', [UserAttendanceController::class, 'clockIn'])->name('user.attendance.clockIn');
    Route::post('/attendance/clock-out', [UserAttendanceController::class, 'clockOut'])->name('user.attendance.clockOut');
    Route::post('/attendance/break-in', [UserAttendanceController::class, 'breakIn'])->name('user.attendance.breakIn');
    Route::post('/attendance/break-out', [UserAttendanceController::class, 'breakOut'])->name('user.attendance.breakOut');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance/list', [UserAttendanceController::class, 'index'])->name('user.attendance.index');
    Route::get('/attendance/{id}', [UserAttendanceController::class, 'show'])
        ->name('user.attendance.show');

    Route::get('application/{id}', [ApplicationController::class, 'show'])
        ->name('user.application.show');

    Route::get('/stamp_correction_request/list', [UserApplicationController::class, 'index'])
    ->name('user.application.index');
    Route::post('/stamp_correction_request/list', [UserApplicationController::class, 'resubmit'])
        ->name('user.application.resubmit');
});




Route::get('/admin/login', [AuthController::class, 'AdminLogin']);
Route::post('/admin/login', [AuthController::class, 'adminAuthenticate'])
    ->name('admin.authenticate');

Route::get('/admin/attendances', [AdminAttendanceController::class, 'index'])
    ->name('admin.attendances.index');
Route::get('/admin/attendances/{id}', [AdminAttendanceController::class, 'show'])
    ->name('admin.attendance.show');


Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])
    ->name('admin.staff.index');
Route::get('/admin/attendances/staff/{id}',[AdminStaffController::class, 'show'])
    ->name('admin.staff.attendance.show');

Route::get('/admin/requests', [AdminApplicationController::class, 'index'])
    ->name('admin.application.index');


Route::get('/admin/requests/{id}', [AdminApprovalController::class, 'edit'])
    ->name('admin.requests.edit');
Route::post('/admin/approval/approve', [AdminApprovalController::class, 'approve'])
    ->name('admin.approval.approve');

Route::put('/admin/attendances/staff/{id}', [AdminAttendanceController::class, 'update'])
    ->name('admin.staff.attendance.update');



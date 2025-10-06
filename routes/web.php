<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelListController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TransportationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CEOController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    // return view('travellist.request');
    // return view('travellist.tvlreport');
    return view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Users
Route::resource('users', UserController::class)->middleware(['auth', 'verified']);

// Employees
Route::resource('employee', EmployeesController::class)
    ->middleware(['auth', 'verified']);

Route::get('/employees', [EmployeesController::class, 'getEmployees'])
    ->middleware(['auth', 'verified'])
    ->name('getEmployees');

// Faculty
Route::resource('faculties', FacultyController::class);

// Travel List
Route::resource('travellist', TravelListController::class)->middleware(['auth', 'verified']);

Route::post('/travellist/{id}/supervisor-approve', [TravelListController::class, 'supervisorApprove'])
    ->name('travellist.supervisor.approve')
    ->middleware('auth');

Route::post('/travellist/{id}/ceo-approve', [TravelListController::class, 'ceoApprove'])
    ->name('travellist.ceo.approve')
    ->middleware('auth');

// Report Route
Route::get('/report/{id}/download', [ReportController::class, 'download'])->name('report.download');


// Route::get('/travellist/{id}/preview', [TravelListController::class, 'preview'])->name('travellist.preview');

Route::get('/report/{id}/preview', [ReportController::class, 'preview'])->name('report.preview');

// Departments
Route::resource('departments', DepartmentController::class)->middleware(['auth', 'verified']);

// Transportation
Route::resource('transportation', TransportationController::class)->middleware(['auth', 'verified']);

// CEO
Route::resource('ceos', CEOController::class);

// Notification

Route::resource('notifications', NotificationController::class);

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'clearAll'])->name('notifications.clear');
});

require __DIR__.'/auth.php';

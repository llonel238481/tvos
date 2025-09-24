<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelListController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TransportationController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
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

Route::get('/travellist/{id}/download', [App\Http\Controllers\TravelListController::class, 'download'])->name('travellist.download');

// Departments
Route::resource('departments', DepartmentController::class)->middleware(['auth', 'verified']);

// Transportation
Route::resource('transportation', TransportationController::class)->middleware(['auth', 'verified']);


require __DIR__.'/auth.php';

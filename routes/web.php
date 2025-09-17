<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelListController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacultyController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
require __DIR__.'/auth.php';

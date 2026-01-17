<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;



Route::get('/', function () {
    return redirect('/admin/login');
});

// Authentication routes
Route::get('/admin/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/admin/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('leads/bulk-delete', [App\Http\Controllers\Admin\LeadController::class, 'bulkDelete'])->name('leads.bulk-delete');
    Route::resource('leads', App\Http\Controllers\Admin\LeadController::class)->except(['show']);
    
    Route::post('personal-leads/bulk-delete', [App\Http\Controllers\Admin\PersonalLeadController::class, 'bulkDelete'])->name('personal-leads.bulk-delete');
    Route::resource('personal-leads', App\Http\Controllers\Admin\PersonalLeadController::class)->except(['show']);

    Route::post('export', [App\Http\Controllers\Admin\ExportController::class, 'export'])->name('export');
});

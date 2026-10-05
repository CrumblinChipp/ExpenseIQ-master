<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SettingsController;

// Livewire
use App\Livewire\Expenses\Index;
use App\Livewire\Reports\Index as ReportIndex;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
  
    //  Onboarding
    Route::get('/welcome', [OnboardingController::class, 'step1'])
        ->name('welcome.step1');
    Route::post('/welcome', [OnboardingController::class, 'saveStep1'])
        ->name('welcome.step1.save');
    Route::get('/welcome/budget', [OnboardingController::class, 'step2'])
        ->name('welcome.step2');
    Route::post('/welcome/budget', [OnboardingController::class, 'saveStep2'])
        ->name('welcome.step2.save');
    Route::get('/welcome/finish', [OnboardingController::class, 'step3'])
        ->name('welcome.step3');
    Route::post('/welcome/finish', [OnboardingController::class, 'finish'])
        ->name('welcome.finish');

   
    //  Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    //  Expenses (Livewire)
    Route::get('/expenses', Index::class)
        ->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])
        ->name('expenses.store');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])
        ->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->name('expenses.destroy');

    //  Budget
    Route::get('/budget', [BudgetController::class, 'index'])
        ->name('budget.index');
    Route::post('/budget/save', [BudgetController::class, 'save'])
        ->name('budget.save');

    //  Reports (Livewire)
    Route::get('/reports', ReportIndex::class)
        ->name('reports.index');
    Route::get('/reports/export/csv', [ExportController::class, 'csv'])
        ->name('reports.export.csv');
    Route::get('/reports/export/pdf', [ExportController::class, 'pdf'])
        ->name('reports.export.pdf');

    //  Settings
    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings');
    Route::get('/settings/password', [SettingsController::class, 'password'])
        ->name('settings.password.page');
    Route::get('/settings/danger', [SettingsController::class, 'danger'])
        ->name('settings.danger.page');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])
        ->name('settings.profile');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])
        ->name('settings.password.update');
    Route::delete('/settings/clear-data', [SettingsController::class, 'clearData'])
        ->name('settings.clear-data');
    Route::delete('/settings/delete-account', [SettingsController::class, 'deleteAccount'])
        ->name('settings.delete');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GrievanceController as AdminGrievanceController;
use App\Http\Controllers\Admin\PropertyTaxController;
use App\Http\Controllers\Admin\QuickLinkController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\TaxPaymentController;
use App\Http\Controllers\Admin\TaxCollectionController;
use App\Http\Controllers\Admin\PaymentManagementController;
use App\Http\Controllers\Admin\WaterTaxController;
use App\Http\Controllers\Citizen\PaymentHistoryController;
use App\Http\Controllers\Citizen\AuthController as CitizenAuthController;
use App\Http\Controllers\Citizen\DashboardController as CitizenDashboardController;
use App\Http\Controllers\GrievanceController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-conditions', [HomeController::class, 'termsConditions'])->name('terms-conditions');
Route::get('/refund-policy', [HomeController::class, 'refundPolicy'])->name('refund-policy');

/*
|--------------------------------------------------------------------------
| Grievance Redressal Routes (Public)
|--------------------------------------------------------------------------
*/

Route::prefix('grievance')->name('grievance.')->group(function () {
    Route::get('/submit', [GrievanceController::class, 'create'])->name('create');
    Route::post('/submit', [GrievanceController::class, 'store'])->name('store');
    Route::get('/success', [GrievanceController::class, 'success'])->name('success');
    Route::get('/track', [GrievanceController::class, 'track'])->name('track');
    Route::post('/status', [GrievanceController::class, 'status'])->name('status');
});

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (Protected)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Water Tax Management (Separate Dashboard)
    Route::get('/water-tax', [WaterTaxController::class, 'index'])->name('water-tax.index');
    Route::get('/water-tax/create', [WaterTaxController::class, 'create'])->name('water-tax.create');
    Route::post('/water-tax', [WaterTaxController::class, 'store'])->name('water-tax.store');
    Route::get('/water-tax/export', [WaterTaxController::class, 'export'])->name('water-tax.export');
    Route::get('/water-tax/{waterTaxRecord}', [WaterTaxController::class, 'show'])->name('water-tax.show');
    Route::get('/water-tax/{waterTaxRecord}/edit', [WaterTaxController::class, 'edit'])->name('water-tax.edit');
    Route::put('/water-tax/{waterTaxRecord}', [WaterTaxController::class, 'update'])->name('water-tax.update');
    Route::delete('/water-tax/{waterTaxRecord}', [WaterTaxController::class, 'destroy'])->name('water-tax.destroy');

    // Property Tax Management (Separate Dashboard)
    Route::get('/property-tax', [PropertyTaxController::class, 'index'])->name('property-tax.index');
    Route::get('/property-tax/create', [PropertyTaxController::class, 'create'])->name('property-tax.create');
    Route::post('/property-tax', [PropertyTaxController::class, 'store'])->name('property-tax.store');
    Route::get('/property-tax/export', [PropertyTaxController::class, 'export'])->name('property-tax.export');
    Route::get('/property-tax/{propertyTaxRecord}', [PropertyTaxController::class, 'show'])->name('property-tax.show');
    Route::get('/property-tax/{propertyTaxRecord}/edit', [PropertyTaxController::class, 'edit'])->name('property-tax.edit');
    Route::put('/property-tax/{propertyTaxRecord}', [PropertyTaxController::class, 'update'])->name('property-tax.update');
    Route::delete('/property-tax/{propertyTaxRecord}', [PropertyTaxController::class, 'destroy'])->name('property-tax.destroy');

    // Tax Payments (Legacy)
    Route::get('/tax-payments', [TaxPaymentController::class, 'index'])->name('tax-payments.index');
    Route::get('/tax-payments/export', [TaxPaymentController::class, 'export'])->name('tax-payments.export');
    Route::get('/tax-payments/{taxPayment}', [TaxPaymentController::class, 'show'])->name('tax-payments.show');

    // Monthly Tax Collection (New System)
    Route::get('/tax-collection', [TaxCollectionController::class, 'index'])->name('tax-collection.index');
    Route::get('/tax-collection/generate', [TaxCollectionController::class, 'generateMonthlyBills'])->name('tax-collection.generate');
    Route::post('/tax-collection/generate', [TaxCollectionController::class, 'storeMonthlyBills'])->name('tax-collection.store-bills');
    Route::post('/tax-collection/{bill}/pay', [TaxCollectionController::class, 'markAsPaid'])->name('tax-collection.pay');
    Route::put('/tax-collection/{bill}/status', [TaxCollectionController::class, 'updateStatus'])->name('tax-collection.update-status');

    // Payments Panel
    Route::get('/payments', [PaymentManagementController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentManagementController::class, 'show'])->name('payments.show');

    // Grievances Management
    Route::get('/grievances', [AdminGrievanceController::class, 'index'])->name('grievances.index');
    Route::get('/grievances/{grievance}', [AdminGrievanceController::class, 'show'])->name('grievances.show');
    Route::put('/grievances/{grievance}/status', [AdminGrievanceController::class, 'updateStatus'])->name('grievances.update-status');
    Route::delete('/grievances/{grievance}', [AdminGrievanceController::class, 'destroy'])->name('grievances.destroy');

    // Content Management
    Route::resource('sliders', SliderController::class);
    Route::resource('quick-links', QuickLinkController::class);

    // Role Management (Super Admin Only)
    Route::resource('roles', RoleController::class);

    // Admin Management (Super Admin Only)
    Route::get('/admins', [AdminController::class, 'index'])->name('admins.index');
    Route::get('/admins/create', [AdminController::class, 'create'])->name('admins.create');
    Route::post('/admins', [AdminController::class, 'store'])->name('admins.store');
    Route::get('/admins/{admin}/edit', [AdminController::class, 'edit'])->name('admins.edit');
    Route::put('/admins/{admin}', [AdminController::class, 'update'])->name('admins.update');
    Route::delete('/admins/{admin}', [AdminController::class, 'destroy'])->name('admins.destroy');
    Route::post('/admins/{admin}/impersonate', [AdminController::class, 'impersonate'])->name('admins.impersonate');
    Route::post('/admins/stop-impersonate', [AdminController::class, 'stopImpersonate'])->name('admins.stop-impersonate');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/general', [SettingsController::class, 'general'])->name('settings.general');
    Route::get('/settings/social', [SettingsController::class, 'social'])->name('settings.social');
    Route::get('/settings/payment', [SettingsController::class, 'payment'])->name('settings.payment');
});

/*
|--------------------------------------------------------------------------
| Citizen Portal Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('citizen')->name('citizen.')->group(function () {
    // Auth routes (public)
    Route::get('/login', [CitizenAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/send-otp', [CitizenAuthController::class, 'sendOtp'])->name('send-otp');
    Route::post('/verify-otp', [CitizenAuthController::class, 'verifyOtp'])->name('verify-otp');
    Route::post('/logout', [CitizenAuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Citizen Portal Routes (Protected)
|--------------------------------------------------------------------------
*/

Route::prefix('citizen')->name('citizen.')->middleware('citizen.auth')->group(function () {
    // Dashboard
    Route::get('/', [CitizenDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [CitizenDashboardController::class, 'index'])->name('dashboard.index');

    // Tax Details
    Route::get('/water-tax', [CitizenDashboardController::class, 'waterTax'])->name('water-tax');
    Route::get('/property-tax', [CitizenDashboardController::class, 'propertyTax'])->name('property-tax');

    // Pay Bill (View)
    Route::get('/pay-bill', [CitizenDashboardController::class, 'payBill'])->name('pay-bill');
    Route::get('/transactions', [PaymentHistoryController::class, 'index'])->name('transactions');
    Route::get('/transactions/{payment}', [PaymentHistoryController::class, 'show'])->name('transactions.show');
    Route::get('/payment-history', [PaymentHistoryController::class, 'index'])->name('payment-history'); // Keep for backward compatibility if needed
    
    // Payment Processing (PhonePe v2)
    Route::post('/payment/initiate', [\App\Http\Controllers\Citizen\PaymentController::class, 'initiatePayment'])->name('payment.initiate');
    Route::get('/payment/redirect', [\App\Http\Controllers\Citizen\PaymentController::class, 'redirect'])->name('payment.redirect');
    Route::post('/payment/check-status', [\App\Http\Controllers\Citizen\PaymentController::class, 'checkStatus'])->name('payment.check-status');

    // Profile
    Route::get('/profile', [CitizenDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [CitizenDashboardController::class, 'updateProfile'])->name('profile.update');
});

// Payment Callback (No auth required - S2S callback from PhonePe)
Route::post('/citizen/payment/callback', [\App\Http\Controllers\Citizen\PaymentController::class, 'callback'])
    ->name('citizen.payment.callback')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

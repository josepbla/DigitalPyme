<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DiagnosticRequestController as AdminDiagnosticRequestController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\SocialPostController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DiagnosticRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\PaymentResultController;

Route::get('/diagnostico', [DiagnosticRequestController::class, 'create'])
    ->name('diagnostics.create');
Route::post('/diagnostico', [DiagnosticRequestController::class, 'store'])
    ->name('diagnostics.store');
Route::post('/checkout/mercadopago', [CheckoutController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('checkout.store');
Route::get('/checkout/resultado/{token}', [PaymentResultController::class, 'show'])
    ->name('payments.result');
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->middleware('throttle:120,1')
    ->name('payments.webhook');

Route::view('/politica-de-privacidad', 'privacy-policy')
    ->name('privacy-policy');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('admin.login.store');
});

Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::patch('/actividad/{type}/{id}/estado', [AdminDashboardController::class, 'updateStatus'])
        ->whereIn('type', ['diagnostic', 'contract'])
        ->name('activity.status.update');
    Route::get('/redes-sociales', [SocialPostController::class, 'index'])->name('social.index');
    Route::post('/redes-sociales', [SocialPostController::class, 'store'])->name('social.store');
    Route::get('/redes-sociales/{socialPost}/imagen', [SocialPostController::class, 'image'])->name('social.image');
    Route::patch('/redes-sociales/{socialPost}/publicacion', [SocialPostController::class, 'updatePublication'])->name('social.publication.update');
    Route::patch('/redes-sociales/{socialPost}/metricas', [SocialPostController::class, 'updateMetrics'])->name('social.metrics.update');
    Route::get('/solicitudes', [AdminDiagnosticRequestController::class, 'index'])->name('requests.index');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
});

Route::get('/', [HomeController::class, 'index'])->name('home');

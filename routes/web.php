<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverProfileController;
use App\Http\Controllers\InternalMessageController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\PaymentController;

Route::get('/', [AuthController::class, 'welcome'])->name('welcome');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Dashboard por rol
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/pasajero', [DashboardController::class, 'pasajero'])->name('dashboard.pasajero');
Route::get('/dashboard/conductor', [DashboardController::class, 'conductor'])->name('dashboard.conductor');
Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');

// Perfil del conductor
Route::get('/conductor/perfil', [DriverProfileController::class, 'show'])->name('driver-profile.show');
Route::post('/conductor/perfil', [DriverProfileController::class, 'update'])->name('driver-profile.update');
Route::get('/admin/conductores/{profileId}', [DriverProfileController::class, 'detail'])->name('driver-profile.detail');
Route::post('/admin/conductores/{profileId}/aprobar', [DriverProfileController::class, 'approve'])->name('driver-profile.approve');
Route::post('/admin/conductores/{profileId}/rechazar', [DriverProfileController::class, 'reject'])->name('driver-profile.reject');

// Viajes
Route::get('/viaje/solicitar', [TripController::class, 'createRequest'])->name('trip.create-request');
Route::post('/viaje/solicitar', [TripController::class, 'storeRequest'])->name('trip.store-request');
Route::get('/ubicaciones/departamentos', [TripController::class, 'departments'])->name('locations.departments');
Route::get('/ubicaciones/departamentos/{departmentId}/municipios', [TripController::class, 'municipalities'])->name('locations.municipalities');
Route::get('/viaje/{id}', [TripController::class, 'show'])->name('trip.show');
Route::get('/viajes/disponibles', [TripController::class, 'listPending'])->name('trip.list-pending');
Route::post('/viaje/{id}/aceptar', [TripController::class, 'accept'])->name('trip.accept');
Route::post('/viaje/{id}/iniciar', [TripController::class, 'start'])->name('trip.start');
Route::post('/viaje/{id}/completar', [TripController::class, 'complete'])->name('trip.complete');
Route::get('/historial-viajes', [TripController::class, 'history'])->name('trip.history');

// Pagos
Route::get('/pago/{tripId}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/pago/{tripId}', [PaymentController::class, 'process'])->name('payment.process');
Route::get('/ganancias', [PaymentController::class, 'earnings'])->name('payment.earnings');
Route::get('/admin/reportes/pagos', [PaymentController::class, 'adminReport'])->name('payment.admin-report');

// Mensajeria interna (admin <-> conductor)
Route::get('/mensajes', [InternalMessageController::class, 'index'])->name('messages.index');
Route::post('/mensajes', [InternalMessageController::class, 'store'])->name('messages.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

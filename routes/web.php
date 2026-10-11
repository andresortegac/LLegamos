<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverProfileController;
use App\Http\Controllers\InternalMessageController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DriverAvailabilityController;
use App\Http\Controllers\TripRatingController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\TripSafetyController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\IncidentAdminController;
use App\Http\Controllers\DriverPayoutController;
use App\Http\Controllers\PayoutAdminController;
use App\Http\Controllers\AccountSecurityController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\RiskOperationsController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\WompiWebhookController;
use App\Http\Controllers\PaymentRefundController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\PrivacyController;

Route::get('/', [AuthController::class, 'welcome'])->name('welcome');
Route::get('/health/ready', [HealthController::class, 'ready'])->middleware('throttle:30,1')->name('health.ready');
Route::view('/privacidad','legal.privacy',['title'=>'Privacidad'])->name('legal.privacy');
Route::view('/terminos','legal.terms',['title'=>'Terminos'])->name('legal.terms');
Route::view('/eliminar-cuenta','legal.deletion',['title'=>'Eliminar cuenta'])->name('legal.deletion');
Route::get('/viaje-compartido/{token}', [TripSafetyController::class, 'shared'])->middleware('throttle:60,1')->name('trip.shared');
Route::post('/webhooks/wompi', WompiWebhookController::class)->middleware('throttle:120,1')->name('webhooks.wompi');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.submit');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::get('/olvide-contrasena', [AccountSecurityController::class, 'forgot'])->name('password.request');
    Route::post('/olvide-contrasena', [AccountSecurityController::class, 'sendReset'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [AccountSecurityController::class, 'resetForm'])->name('password.reset');
    Route::post('/restablecer-contrasena', [AccountSecurityController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/verificar-correo', [AccountSecurityController::class, 'verificationNotice'])->name('verification.notice');
    Route::get('/verificar-correo/{id}/{hash}', [AccountSecurityController::class, 'verify'])->middleware(['signed','throttle:6,1'])->name('verification.verify');
    Route::post('/verificar-correo/reenviar', [AccountSecurityController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth','active','verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/soporte', [SupportController::class, 'index'])->name('support.index');
    Route::post('/soporte', [SupportController::class, 'store'])->middleware('throttle:5,1')->name('support.store');
    Route::post('/soporte/{ticket}/responder', [SupportController::class, 'reply'])->middleware('throttle:20,1')->name('support.reply');
    Route::get('/mis-datos', [PrivacyController::class, 'portal'])->name('privacy.portal');
    Route::get('/mis-datos/exportar', [PrivacyController::class, 'export'])->middleware('throttle:3,1')->name('privacy.export');
    Route::post('/mis-datos/solicitud', [PrivacyController::class, 'request'])->middleware('throttle:3,1')->name('privacy.request');
    Route::get('/push/public-key', [PushSubscriptionController::class, 'key'])->name('push.key');
    Route::post('/push/subscriptions', [PushSubscriptionController::class, 'store'])->middleware('throttle:10,1')->name('push.store');
    Route::delete('/push/subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push.destroy');

    Route::get('/ubicaciones/departamentos', [TripController::class, 'departments'])->name('locations.departments');
    Route::get('/ubicaciones/departamentos/{departmentId}/municipios', [TripController::class, 'municipalities'])->name('locations.municipalities');
    Route::get('/viaje/{id}', [TripController::class, 'show'])->whereNumber('id')->name('trip.show');
    Route::get('/viaje/{id}/estado', [TripController::class, 'liveStatus'])->whereNumber('id')->middleware('throttle:60,1')->name('trip.live-status');
    Route::post('/viaje/{id}/cancelar', [TripController::class, 'cancel'])->whereNumber('id')->middleware('role:pasajero,conductor')->name('trip.cancel');
    Route::get('/historial-viajes', [TripController::class, 'history'])->middleware('role:pasajero,conductor')->name('trip.history');
    Route::post('/viaje/{tripId}/calificar', [TripRatingController::class, 'store'])->whereNumber('tripId')->middleware('role:pasajero,conductor')->name('trip.rating.store');
    Route::get('/notificaciones', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/seguridad/contactos', [EmergencyContactController::class, 'index'])->name('safety.contacts');
    Route::post('/seguridad/contactos', [EmergencyContactController::class, 'store'])->name('safety.contacts.store');
    Route::delete('/seguridad/contactos/{id}', [EmergencyContactController::class, 'destroy'])->whereNumber('id')->name('safety.contacts.destroy');
    Route::post('/viaje/{tripId}/compartir', [TripSafetyController::class, 'share'])->whereNumber('tripId')->name('trip.share');
    Route::delete('/viaje/{tripId}/compartir', [TripSafetyController::class, 'revoke'])->whereNumber('tripId')->name('trip.share.revoke');
    Route::post('/viaje/{tripId}/sos', [TripSafetyController::class, 'sos'])->whereNumber('tripId')->middleware('throttle:3,1')->name('trip.sos');
    Route::post('/viaje/{tripId}/incidente', [TripSafetyController::class, 'report'])->whereNumber('tripId')->name('trip.incident.report');

    Route::get('/conductor/documentos/{profileId}/{document}', [DriverProfileController::class, 'downloadDocument'])
        ->whereNumber('profileId')
        ->whereIn('document', ['profile', 'license', 'property-card', 'soat', 'id-card', 'technical-review', 'owner-authorization'])
        ->name('driver-profile.document');

    Route::middleware('role:pasajero')->group(function () {
        Route::get('/dashboard/pasajero', [DashboardController::class, 'pasajero'])->name('dashboard.pasajero');
        Route::get('/viaje/solicitar', [TripController::class, 'createRequest'])->name('trip.create-request');
        Route::post('/viaje/solicitar', [TripController::class, 'storeRequest'])->middleware('throttle:10,1')->name('trip.store-request');
        Route::get('/pago/{tripId}', [PaymentController::class, 'show'])->whereNumber('tripId')->name('payment.show');
        Route::post('/pago/{tripId}', [PaymentController::class, 'process'])->whereNumber('tripId')->middleware('throttle:5,1')->name('payment.process');
        Route::post('/pago/{tripId}/wompi', [PaymentController::class, 'wompiCheckout'])->whereNumber('tripId')->middleware('throttle:5,1')->name('payment.wompi');
        Route::get('/pago/{tripId}/wompi/respuesta', [PaymentController::class, 'wompiReturn'])->whereNumber('tripId')->name('payment.wompi.return');
    });

    Route::middleware('role:conductor')->group(function () {
        Route::get('/dashboard/conductor', [DashboardController::class, 'conductor'])->name('dashboard.conductor');
        Route::get('/conductor/perfil', [DriverProfileController::class, 'show'])->name('driver-profile.show');
        Route::post('/conductor/perfil', [DriverProfileController::class, 'update'])->name('driver-profile.update');
        Route::get('/conductor/verificacion/desafio', [DriverProfileController::class, 'livenessChallenge'])->middleware('throttle:10,1')->name('driver-profile.liveness');
        Route::get('/viajes/disponibles', [TripController::class, 'listPending'])->name('trip.list-pending');
        Route::post('/viaje/{id}/aceptar', [TripController::class, 'accept'])->whereNumber('id')->name('trip.accept');
        Route::post('/viaje/{id}/rechazar-oferta', [TripController::class, 'rejectOffer'])->whereNumber('id')->name('trip.offer.reject');
        Route::post('/viaje/{id}/iniciar', [TripController::class, 'start'])->whereNumber('id')->name('trip.start');
        Route::post('/viaje/{id}/completar', [TripController::class, 'complete'])->whereNumber('id')->name('trip.complete');
        Route::get('/ganancias', [PaymentController::class, 'earnings'])->name('payment.earnings');
        Route::post('/conductor/disponibilidad', [DriverAvailabilityController::class, 'update'])->name('driver.availability');
        Route::post('/conductor/ubicacion', [DriverAvailabilityController::class, 'location'])->middleware('throttle:30,1')->name('driver.location');
        Route::get('/conductor/retiros', [DriverPayoutController::class, 'index'])->name('payouts.driver');
        Route::post('/conductor/retiros/cuenta', [DriverPayoutController::class, 'account'])->name('payouts.account');
        Route::post('/conductor/retiros/solicitar', [DriverPayoutController::class, 'request'])->middleware('throttle:3,1')->name('payouts.request');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
        Route::get('/admin/conductores/{profileId}', [DriverProfileController::class, 'detail'])->whereNumber('profileId')->name('driver-profile.detail');
        Route::post('/admin/conductores/{profileId}/aprobar', [DriverProfileController::class, 'approve'])->whereNumber('profileId')->name('driver-profile.approve');
        Route::post('/admin/conductores/{profileId}/rechazar', [DriverProfileController::class, 'reject'])->whereNumber('profileId')->name('driver-profile.reject');
        Route::get('/admin/reportes/pagos', [PaymentController::class, 'adminReport'])->name('payment.admin-report');
        Route::post('/admin/pagos/{paymentId}/conciliar', [PaymentController::class, 'reconcile'])->whereNumber('paymentId')->name('payment.reconcile');
        Route::post('/admin/pagos/{payment}/reembolsar', [PaymentRefundController::class, 'store'])->name('payment.refund');
        Route::get('/admin/incidentes', [IncidentAdminController::class, 'index'])->name('admin.incidents.index');
        Route::get('/admin/incidentes/{id}', [IncidentAdminController::class, 'show'])->whereNumber('id')->name('admin.incidents.show');
        Route::patch('/admin/incidentes/{id}', [IncidentAdminController::class, 'update'])->whereNumber('id')->name('admin.incidents.update');
        Route::get('/admin/retiros', [PayoutAdminController::class, 'index'])->name('admin.payouts.index');
        Route::patch('/admin/retiros/{id}', [PayoutAdminController::class, 'update'])->whereNumber('id')->name('admin.payouts.update');
        Route::get('/admin/usuarios', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::patch('/admin/usuarios/{user}/suspender', [AdminUserController::class, 'suspend'])->name('admin.users.suspend');
        Route::patch('/admin/usuarios/{user}/reactivar', [AdminUserController::class, 'reactivate'])->name('admin.users.reactivate');
        Route::get('/admin/auditoria', [AuditLogController::class, 'index'])->name('admin.audit.index');
        Route::get('/admin/riesgo', [RiskOperationsController::class, 'index'])->name('admin.risk.index');
        Route::patch('/soporte/{ticket}', [SupportController::class, 'update'])->name('support.update');
        Route::get('/admin/privacidad', [PrivacyController::class, 'admin'])->name('admin.privacy');
        Route::patch('/admin/privacidad/{privacyRequest}', [PrivacyController::class, 'resolve'])->name('admin.privacy.resolve');
        Route::patch('/admin/riesgo/{alert}', [RiskOperationsController::class, 'update'])->name('admin.risk.update');
    });

    Route::middleware('role:admin,conductor')->group(function () {
        Route::get('/mensajes', [InternalMessageController::class, 'index'])->name('messages.index');
        Route::post('/mensajes', [InternalMessageController::class, 'store'])->middleware('throttle:30,1')->name('messages.store');
    });
});
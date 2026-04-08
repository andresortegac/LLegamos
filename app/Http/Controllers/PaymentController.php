<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class PaymentController extends Controller
{
    /**
     * Show payment form for a trip
     */
    public function show($tripId)
    {
        if (!Session::has('user')) {
            return Redirect::route('login');
        }

        $user = Session::get('user');
        $trip = Trip::find($tripId);

        if (!$trip) {
            return Redirect::route('dashboard.' . $user['role'])->with('error', 'Viaje no encontrado');
        }

        if ($trip->passenger_id !== $user['id']) {
            return Redirect::route('trip.show', $tripId)->with('error', 'Solo el pasajero puede pagar este viaje');
        }

        if ($trip->status !== 'completed') {
            return Redirect::route('trip.show', $tripId)->with('error', 'Solo puedes pagar un viaje completado');
        }

        $existingPayment = Payment::where('trip_id', $tripId)->first();
        if ($existingPayment && $existingPayment->status === 'completed') {
            return Redirect::route('trip.show', $tripId)->with('success', 'Este viaje ya ha sido pagado');
        }

        return view('payment.checkout', [
            'user' => $user,
            'trip' => $trip,
        ]);
    }

    /**
     * Process payment for a trip
     */
    public function process(Request $request, $tripId)
    {
        if (!Session::has('user')) {
            return Redirect::route('login');
        }

        $user = Session::get('user');
        $trip = Trip::find($tripId);

        if (!$trip || $trip->passenger_id !== $user['id'] || $trip->status !== 'completed') {
            return Redirect::route('trip.show', $tripId)->with('error', 'Pago inválido');
        }

        $amount = $trip->final_cost ?? $trip->estimated_cost;

        $payment = Payment::firstOrCreate(
            ['trip_id' => $tripId],
            [
                'user_id' => $user['id'],
                'amount' => $amount,
                'description' => "Pago por viaje #" . $trip->id,
                'status' => 'completed',
            ]
        );

        if ($payment->status === 'completed') {
            return Redirect::route('trip.show', $tripId)->with('success', 'Pago procesado exitosamente');
        }

        $payment->update(['status' => 'completed']);

        return Redirect::route('trip.show', $tripId)->with('success', 'Pago procesado exitosamente. Gracias por usar Llegamos.');
    }

    /**
     * Get conductor earnings dashboard
     */
    public function earnings()
    {
        if (!Session::has('user')) {
            return Redirect::route('login');
        }

        $user = Session::get('user');

        if ($user['role'] !== 'conductor') {
            return Redirect::route('dashboard.' . $user['role'])->with('error', 'Acceso denegado');
        }

        $driver = User::with('driverProfile')->find($user['id']);
        $profile = $driver?->driverProfile;

        if (!$profile || $profile->verification_status !== 'approved') {
            return Redirect::route('driver-profile.show')
                ->with('error', 'Debes completar tu perfil y esperar aprobacion del administrador para acceder a ganancias.');
        }

        $trips = Trip::where('driver_id', $user['id'])
            ->where('status', 'completed')
            ->with('passenger:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalEarnings = 0;
        $completedTrips = 0;
        $pendingPayments = 0;

        foreach ($trips as $trip) {
            $payment = Payment::where('trip_id', $trip->id)->first();
            $amount = $trip->final_cost ?? $trip->estimated_cost;

            if ($payment && $payment->status === 'completed') {
                $totalEarnings += $amount;
                $completedTrips++;
            } else {
                $pendingPayments += $amount;
            }
        }

        return view('payment.earnings', [
            'user' => $user,
            'trips' => $trips,
            'totalEarnings' => $totalEarnings,
            'completedTrips' => $completedTrips,
            'pendingPayments' => $pendingPayments,
        ]);
    }

    /**
     * Get all payments for admin
     */
    public function adminReport()
    {
        if (!Session::has('user')) {
            return Redirect::route('login');
        }

        $user = Session::get('user');

        if ($user['role'] !== 'admin') {
            return Redirect::route('dashboard.admin')->with('error', 'Acceso denegado');
        }

        $payments = Payment::with('trip', 'user')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'totalPayments' => $payments->count(),
            'totalAmount' => $payments->where('status', 'completed')->sum('amount'),
            'completedPayments' => $payments->where('status', 'completed')->count(),
            'pendingPayments' => $payments->where('status', 'pending')->count(),
            'failedPayments' => $payments->where('status', 'failed')->count(),
        ];

        return view('payment.admin-report', [
            'user' => $user,
            'payments' => $payments,
            'stats' => $stats,
        ]);
    }
}

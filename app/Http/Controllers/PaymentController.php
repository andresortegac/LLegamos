<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Trip;
use App\Models\User;
use App\Models\DriverEarning;
use App\Services\MonetizationService;
use App\Services\WompiPaymentService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class PaymentController extends Controller
{
    public function __construct(private readonly MonetizationService $monetization, private readonly WompiPaymentService $wompi) {}

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
            'wompiEnabled' => $this->wompi->configured(),
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

        $validated = $request->validate([
            'payment_method' => 'required|in:cash',
        ]);

        $amount = $trip->final_cost ?? $trip->estimated_cost;

        $payment = Payment::firstOrCreate(
            ['trip_id' => $tripId],
            [
                'user_id' => $user['id'],
                'amount' => $amount,
                'description' => 'Pago en efectivo del viaje #' . $trip->id,
                'status' => 'pending',
                'notes' => 'Pendiente de conciliacion. No representa un cobro electronico.',
            ]
        );

        if ($payment->status === 'completed') {
            return Redirect::route('trip.show', $tripId)->with('success', 'Este viaje ya fue conciliado.');
        }

        return Redirect::route('trip.show', $tripId)
            ->with('success', 'Pago en efectivo registrado como pendiente de conciliacion.');
    }


    public function wompiCheckout(Request $request, int $tripId)
    {
        $trip=Trip::findOrFail($tripId);abort_unless($trip->passenger_id===$request->user()->id&&$trip->status==='completed',403);
        $amount=(float)($trip->final_cost??$trip->estimated_cost);$cents=(int)round($amount*100);
        $payment=Payment::firstOrCreate(['trip_id'=>$trip->id],['user_id'=>$request->user()->id,'amount'=>$amount,'currency'=>'COP','amount_in_cents'=>$cents,'status'=>'pending','provider'=>'wompi','provider_reference'=>'TRIP-'.$trip->id.'-'.Str::upper(Str::random(16)),'description'=>'Pago electronico viaje #'.$trip->id]);
        abort_if($payment->provider==='cash'&&$payment->status==='pending',409,'Ya existe un pago en efectivo pendiente.');
        abort_if($payment->status==='completed',409,'El viaje ya fue pagado.');
        if(!$payment->provider_reference)$payment->update(['provider'=>'wompi','provider_reference'=>'TRIP-'.$trip->id.'-'.Str::upper(Str::random(16)),'amount_in_cents'=>$cents,'currency'=>'COP']);
        return view('payment.redirect-wompi',['checkout'=>$this->wompi->checkout($payment->load('user')),'trip'=>$trip]);
    }
    public function wompiReturn(Request $request,int $tripId){$trip=Trip::findOrFail($tripId);abort_unless($trip->passenger_id===$request->user()->id,403);return redirect()->route('trip.show',$tripId)->with('success','Wompi recibio el pago. Confirmaremos el resultado de forma segura mediante su notificacion oficial.');}
    public function reconcile(Request $request, int $paymentId)
    {
        $payment = Payment::with('trip')->findOrFail($paymentId);
        if ($payment->status === 'completed') {
            return back()->with('success', 'El pago ya estaba conciliado.');
        }
        $payment->update(['status' => 'completed', 'notes' => trim(($payment->notes ?? '') . ' Conciliado por admin #' . $request->user()->id . ' el ' . now()->toIso8601String())]);
        if ($payment->trip?->status === 'completed') {
            $this->monetization->recordCompletedTrip($payment->trip);
            $this->monetization->makeAvailable($payment->trip_id);
        }
        return back()->with('success', 'Pago conciliado y saldo del conductor liberado.');
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

        $earnings = DriverEarning::where('driver_id', $user['id'])->get();
        $totalEarnings = $earnings->whereIn('status', ['available', 'paid'])->sum('net_amount');
        $pendingPayments = $earnings->where('status', 'pending')->sum('net_amount');
        $completedTrips = $earnings->count();

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

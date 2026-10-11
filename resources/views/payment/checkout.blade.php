<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Pago | Llegamos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            min-height: 100vh;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 500px;
            width: 100%;
        }

        .card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 28px;
            color: #38bdf8;
            margin-bottom: 10px;
        }

        .header p {
            color: #cbd5e1;
            font-size: 14px;
        }

        .trip-summary {
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .trip-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .trip-info:last-child {
            margin-bottom: 0;
        }

        .trip-label {
            color: #cbd5e1;
        }

        .trip-value {
            color: #e2e8f0;
            font-weight: 600;
        }

        .divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 15px 0;
        }

        .total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
            color: #38bdf8;
        }

        .payment-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 14px;
            color: #cbd5e1;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .method {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            font-size: 13px;
        }

        .method:hover {
            border-color: rgba(56, 189, 248, 0.5);
            background: rgba(56, 189, 248, 0.05);
        }

        .method.active {
            border-color: #38bdf8;
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
        }

        .pay-button {
            width: 100%;
            padding: 15px;
            background: #38bdf8;
            color: #0f172a;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .pay-button:hover {
            background: #0ea5e9;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(56, 189, 248, 0.3);
        }

        .pay-button:disabled {
            background: #64748b;
            cursor: not-allowed;
        }

        .security-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            padding: 15px;
            background: rgba(34, 197, 94, 0.1);
            border-radius: 8px;
            font-size: 12px;
            color: #a7f3d0;
        }

        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #93c5fd;
            text-decoration: none;
            font-size: 14px;
        }

        .cancel-link:hover {
            color: #38bdf8;
        }

        .success-badge {
            display: inline-block;
            background: rgba(16, 185, 129, 0.2);
            color: #a7f3d0;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 20px;
        }
    </style>
    @include('partials.pwa-head')
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>💳 Procesar Pago</h1>
                <p>Completa tu pago para finalizar el viaje</p>
            </div>

            <!-- Trip Summary -->
            <div class="trip-summary">
                <div class="trip-info">
                    <span class="trip-label">Viaje ID</span>
                    <span class="trip-value">#{{ $trip->id }}</span>
                </div>
                <div class="trip-info">
                    <span class="trip-label">Origen</span>
                    <span class="trip-value" style="text-align: right; max-width: 50%;">{{ substr($trip->origin, 0, 20) }}...</span>
                </div>
                <div class="trip-info">
                    <span class="trip-label">Destino</span>
                    <span class="trip-value" style="text-align: right; max-width: 50%;">{{ substr($trip->destination, 0, 20) }}...</span>
                </div>
                @if($trip->distance_km)
                    <div class="trip-info">
                        <span class="trip-label">Distancia</span>
                        <span class="trip-value">{{ number_format($trip->distance_km, 2) }} km</span>
                    </div>
                @endif
                <div class="divider"></div>
                <div class="total">
                    <span>Total a pagar:</span>
                    <span>${{ number_format($trip->final_cost ?? $trip->estimated_cost, 2) }}</span>
                </div>
            </div>

            <!-- Payment method: cash only until a certified gateway is configured. -->
            <form method="POST" action="{{ route('payment.process', $trip->id) }}" id="paymentForm">
                @csrf
                <input type="hidden" name="payment_method" value="cash">

                <div class="payment-section">
                    <div class="section-title">Pago en efectivo</div>
                    <div style="background: rgba(251, 146, 60, 0.1); border: 1px solid rgba(251, 146, 60, 0.3); padding: 15px; border-radius: 8px; color: #fed7aa; font-size: 13px;">
                        <p>Entrega al conductor el valor indicado. El registro quedara pendiente de conciliacion.</p>
                        <p style="margin-top: 10px;">El pago con tarjeta se habilitara unicamente mediante una pasarela certificada; Llegamos no captura datos de tarjeta.</p>
                    </div>
                </div>

                <button type="submit" class="pay-button">Registrar pago en efectivo</button>
            </form>

            @if($wompiEnabled)
            <form method="POST" action="{{ route('payment.wompi', $trip->id) }}" style="margin-top:16px">@csrf<button type="submit" class="pay-button" style="background:#22c55e">Pagar en linea con Wompi</button></form>
            @else
            <div style="margin-top:16px;color:#94a3b8;font-size:13px">Pago electronico pendiente de credenciales comerciales Wompi.</div>
            @endif

            <!-- Security Info -->
            <div class="security-info">
                🔒 Tus datos están protegidos y cifrados
            </div>

            <!-- Cancel Button -->
            <a href="{{ route('trip.show', $trip->id) }}" class="cancel-link">← Cancelar</a>
        </div>
    </div>


    @include('partials.pwa-script')
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Ganancias | Llegamos</title>
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
        }

        .navbar {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 15px 30px;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .navbar h3 {
            color: #38bdf8;
        }

        .back-link {
            color: #93c5fd;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            color: #38bdf8;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
        }

        .stat-label {
            color: #cbd5e1;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-value {
            color: #38bdf8;
            font-size: 32px;
            font-weight: bold;
        }

        .stat-card.earnings .stat-value {
            color: #10b981;
        }

        .stat-card.trips .stat-value {
            color: #f59e0b;
        }

        .stat-card.pending .stat-value {
            color: #f87171;
        }

        .section-title {
            color: #38bdf8;
            font-size: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 24px;
            background: #38bdf8;
            border-radius: 2px;
        }

        .trips-table {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 15px;
            overflow: hidden;
        }

        .table-header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
            gap: 15px;
            background: rgba(56, 189, 248, 0.1);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            font-weight: bold;
            color: #93c5fd;
            font-size: 12px;
            text-transform: uppercase;
        }

        .table-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            align-items: center;
            font-size: 13px;
            color: #cbd5e1;
            transition: background 0.3s;
        }

        .table-row:hover {
            background: rgba(56, 189, 248, 0.05);
        }

        .table-row:last-child {
            border-bottom: none;
        }

        .trip-id {
            color: #e2e8f0;
            font-weight: 600;
        }

        .passenger-name {
            color: #93c5fd;
        }

        .trip-date {
            color: #94a3b8;
            font-size: 12px;
        }

        .amount {
            color: #10b981;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
        }

        .status-completed {
            background: rgba(16, 185, 129, 0.2);
            color: #a7f3d0;
        }

        .status-pending {
            background: rgba(251, 146, 60, 0.2);
            color: #fed7aa;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
        }

        @media(max-width: 768px) {
            .table-header,
            .table-row {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h3>💰 Mis Ganancias</h3>
        <a href="{{ route('dashboard.conductor') }}" class="back-link">← Volver</a>
    </div>

    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card earnings">
                <div class="stat-label">Ganancias Totales</div>
                <div class="stat-value">${{ number_format($totalEarnings, 2) }}</div>
            </div>
            <div class="stat-card trips">
                <div class="stat-label">Viajes Completados</div>
                <div class="stat-value">{{ $completedTrips }}</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-label">Pagos Pendientes</div>
                <div class="stat-value">${{ number_format($pendingPayments, 2) }}</div>
            </div>
        </div>

        <!-- Trips History -->
        <h2 class="section-title">Historial de Viajes</h2>

        @if($trips->count() > 0)
            <div class="trips-table">
                <div class="table-header">
                    <div>ID Viaje</div>
                    <div>Pasajero</div>
                    <div>Fecha</div>
                    <div>Monto</div>
                    <div>Estado</div>
                </div>

                @foreach($trips as $trip)
                    @php
                        $payment = $trip->payment;
                        $amount = $trip->final_cost ?? $trip->estimated_cost;
                        $status = $payment && $payment->status === 'completed' ? 'completed' : 'pending';
                    @endphp
                    <div class="table-row">
                        <div class="trip-id">#{{ $trip->id }}</div>
                        <div class="passenger-name">{{ $trip->passenger->name }}</div>
                        <div class="trip-date">{{ $trip->created_at->format('d/m/Y H:i') }}</div>
                        <div class="amount">${{ number_format($amount, 2) }}</div>
                        <div>
                            <span class="status-badge status-{{ $status }}">
                                @if($status === 'completed')
                                    ✓ Pagado
                                @else
                                    ⏳ Pendiente
                                @endif
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <p>📭 No tienes viajes completados aún</p>
                <p style="font-size: 12px; color: #64748b; margin-top: 10px;">Comienza a aceptar viajes para ganar dinero</p>
            </div>
        @endif
    </div>
</body>
</html>

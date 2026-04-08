<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viajes Disponibles | Llegamos</title>
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

        .trips-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .trip-card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 25px;
            border-radius: 12px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            align-items: center;
            transition: all 0.3s;
        }

        .trip-card:hover {
            border-color: rgba(56, 189, 248, 0.5);
            background: rgba(56, 189, 248, 0.05);
        }

        .trip-info {
            display: grid;
            gap: 15px;
        }

        .locations {
            display: flex;
            align-items: center;
            gap: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .location {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        .location-icon {
            font-size: 24px;
        }

        .location-text h4 {
            color: #e2e8f0;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .location-text p {
            color: #cbd5e1;
            font-size: 13px;
        }

        .arrow {
            color: #64748b;
        }

        .trip-details {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .detail-label {
            color: #94a3b8;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .detail-value {
            color: #e2e8f0;
            font-size: 14px;
            font-weight: 600;
        }

        .cost-value {
            color: #10b981;
            font-size: 20px;
        }

        .action-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 150px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            white-space: nowrap;
            transition: all 0.3s;
        }

        .btn-accept {
            background: #10b981;
            color: white;
        }

        .btn-accept:hover {
            background: #059669;
        }

        .btn-view {
            background: #38bdf8;
            color: #0f172a;
        }

        .btn-view:hover {
            background: #0ea5e9;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 15px;
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #cbd5e1;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .empty-state p {
            color: #94a3b8;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .trip-card {
                grid-template-columns: 1fr;
            }

            .action-group {
                min-width: unset;
            }

            .trip-details {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h3>🚕 Viajes Disponibles</h3>
        <a href="{{ route('dashboard.conductor') }}" class="back-link">← Volver al panel</a>
    </div>

    <div class="container">
        @if($trips->count() > 0)
            <div class="trips-list">
                @foreach($trips as $trip)
                    <div class="trip-card">
                        <div class="trip-info">
                            <!-- Ubicaciones -->
                            <div class="locations">
                                <div class="location" style="flex: 0.5;">
                                    <div class="location-icon">📍</div>
                                    <div class="location-text">
                                        <h4>Recogida</h4>
                                        <p>{{ substr($trip->origin, 0, 30) }}...</p>
                                    </div>
                                </div>

                                <div class="arrow">→</div>

                                <div class="location" style="flex: 0.5;">
                                    <div class="location-icon">🎯</div>
                                    <div class="location-text">
                                        <h4>Destino</h4>
                                        <p>{{ substr($trip->destination, 0, 30) }}...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Detalles -->
                            <div class="trip-details">
                                <div class="detail-item">
                                    <span class="detail-label">Costo estimado</span>
                                    <span class="detail-value cost-value">${{ number_format($trip->estimated_cost, 2) }}</span>
                                </div>

                                <div class="detail-item">
                                    <span class="detail-label">ID del viaje</span>
                                    <span class="detail-value">#{{ $trip->id }}</span>
                                </div>

                                <div class="detail-item">
                                    <span class="detail-label">Tipo de viaje</span>
                                    <span class="detail-value">{{ ucfirst($trip->vehicle_type ?? 'No definido') }}</span>
                                </div>

                                <div class="detail-item">
                                    <span class="detail-label">Solicitado hace</span>
                                    <span class="detail-value">hace {{ $trip->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="action-group">
                            <form method="POST" action="{{ route('trip.accept', $trip->id) }}" style="width: 100%;">
                                @csrf
                                <button type="submit" class="btn btn-accept">
                                    ✓ Aceptar
                                </button>
                            </form>

                            <a href="{{ route('trip.show', $trip->id) }}" class="btn btn-view">
                                Ver detalles
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">😴</div>
                <h3>No hay viajes disponibles</h3>
                <p>En este momento no hay solicitudes de viaje esperando conductor.</p>
                <p style="margin-top: 10px;">Mantén esta página abierta para ver nuevas solicitudes.</p>
            </div>
        @endif
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Viajes | Llegamos</title>
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
            transition: all 0.3s;
        }

        .trip-card:hover {
            border-color: rgba(56, 189, 248, 0.5);
            background: rgba(56, 189, 248, 0.05);
        }

        .trip-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .trip-id {
            color: #cbd5e1;
            font-size: 14px;
        }

        .trip-date {
            color: #94a3b8;
            font-size: 12px;
        }

        .trip-info {
            display: grid;
            gap: 15px;
        }

        .locations {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .location {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        .location-icon {
            font-size: 20px;
        }

        .location-text h4 {
            color: #e2e8f0;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .location-text p {
            color: #cbd5e1;
            font-size: 12px;
        }

        .arrow {
            color: #64748b;
        }

        .trip-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .detail-label {
            color: #94a3b8;
            font-size: 11px;
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
        }

        .time-value {
            color: #93c5fd;
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

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #38bdf8;
            color: #0f172a;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #0ea5e9;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h3>📋 Historial de Viajes</h3>
        <a href="{{ route('dashboard.' . $user['role']) }}" class="back-link">← Volver</a>
    </div>

    <div class="container">
        @if($trips->count() > 0)
            <div class="trips-list">
                @foreach($trips as $trip)
                    <div class="trip-card">
                        <div class="trip-header">
                            <div>
                                <div class="trip-id">Viaje #{{ $trip->id }}</div>
                                <div class="trip-date">{{ $trip->end_time->format('d/m/Y H:i') }}</div>
                            </div>
                            <span style="display: inline-block; background: rgba(16, 185, 129, 0.2); color: #a7f3d0; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: bold;">✓ Completado</span>
                        </div>

                        <div class="trip-info">
                            <!-- Ubicaciones -->
                            <div class="locations">
                                <div class="location" style="flex: 0.45;">
                                    <div class="location-icon">📍</div>
                                    <div class="location-text">
                                        <h4>Recogida</h4>
                                        <p>{{ substr($trip->origin, 0, 25) }}...</p>
                                    </div>
                                </div>

                                <div class="arrow" style="flex: 0.1; text-align: center;">→</div>

                                <div class="location" style="flex: 0.45;">
                                    <div class="location-icon">🎯</div>
                                    <div class="location-text">
                                        <h4>Destino</h4>
                                        <p>{{ substr($trip->destination, 0, 25) }}...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Detalles -->
                            <div class="trip-details">
                                <div class="detail-item">
                                    <span class="detail-label">Costo</span>
                                    <span class="detail-value cost-value">${{ number_format($trip->final_cost, 2) }}</span>
                                </div>

                                <div class="detail-item">
                                    <span class="detail-label">Duración</span>
                                    <span class="detail-value time-value">
                                        @if($trip->start_time && $trip->end_time)
                                            {{ $trip->start_time->diff($trip->end_time)->format('%H:%I') }} horas
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>

                                @if($trip->distance_km)
                                    <div class="detail-item">
                                        <span class="detail-label">Distancia</span>
                                        <span class="detail-value">{{ $trip->distance_km }} km</span>
                                    </div>
                                @endif

                                <div class="detail-item">
                                    <span class="detail-label">Fecha</span>
                                    <span class="detail-value">{{ $trip->end_time->format('d M Y') }}</span>
                                </div>
                            </div>

                            @if($trip->notes)
                                <div style="margin-top: 10px; padding: 10px; background: rgba(255,255,255,0.05); border-left: 2px solid #38bdf8; border-radius: 4px;">
                                    <p style="color: #cbd5e1; font-size: 12px;"><strong>Notas:</strong> {{ $trip->notes }}</p>
                                </div>
                            @endif

                            <a href="{{ route('trip.show', $trip->id) }}" class="btn">
                                Ver detalles completos
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No tienes viajes completados</h3>
                <p>Tu historial de viajes aparecerá aquí cuando completes alguno.</p>
            </div>
        @endif
    </div>
</body>
</html>

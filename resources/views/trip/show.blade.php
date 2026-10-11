<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Viaje | Llegamos</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
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
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .left-panel {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .right-panel {
            display: flex;
            flex-direction: column;
        }

        #map {
            height: 600px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .map-info {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
        }

        .map-info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
        }

        .map-info-label {
            color: #cbd5e1;
        }

        .map-info-value {
            color: #38bdf8;
            font-weight: bold;
        }

        @media(max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }

            #map {
                height: 400px;
            }
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin: 20px 0;
        }

        .status-pending {
            background: rgba(251, 146, 60, 0.2);
            color: #fed7aa;
            border: 1px solid rgba(251, 146, 60, 0.3);
        }

        .status-accepted {
            background: rgba(96, 165, 250, 0.2);
            color: #bfdbfe;
            border: 1px solid rgba(96, 165, 250, 0.3);
        }

        .status-in-progress {
            background: rgba(34, 197, 94, 0.2);
            color: #bbf7d0;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .status-completed {
            background: rgba(16, 185, 129, 0.2);
            color: #a7f3d0;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .card-title {
            color: #38bdf8;
            font-size: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .card-title::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 24px;
            background: #38bdf8;
            border-radius: 2px;
            margin-right: 10px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #cbd5e1;
            font-size: 14px;
        }

        .detail-value {
            color: #e2e8f0;
            font-weight: 600;
        }

        .location-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(56, 189, 248, 0.05);
            border-left: 3px solid #38bdf8;
            border-radius: 4px;
        }

        .location-icon {
            font-size: 24px;
            margin-right: 15px;
            min-width: 30px;
        }

        .location-text h4 {
            color: #e2e8f0;
            margin-bottom: 5px;
        }

        .location-text p {
            color: #cbd5e1;
            font-size: 14px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #38bdf8;
            color: #0f172a;
            grid-column: 1 / -1;
        }

        .btn-primary:hover {
            background: #0ea5e9;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .success {
            background: rgba(16, 185, 129, 0.2);
            color: #a7f3d0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #94a3b8;
        }

        .waiting-message {
            text-align: center;
            padding: 30px;
            background: rgba(56, 189, 248, 0.05);
            border-radius: 8px;
            margin: 20px 0;
        }

        .waiting-message p {
            color: #cbd5e1;
            margin-bottom: 10px;
        }

        .spinner {
            display: inline-block;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .tracking-card { background: rgba(15,23,42,.94); border: 1px solid rgba(56,189,248,.35); border-radius: 14px; padding: 16px; margin-top: 14px; }
        .tracking-header { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
        .tracking-status { display:inline-flex; align-items:center; gap:7px; font-weight:700; color:#fbbf24; }
        .tracking-status.live { color:#4ade80; }
        .tracking-status.stale { color:#fbbf24; }
        .tracking-dot { width:9px; height:9px; border-radius:50%; background:currentColor; box-shadow:0 0 0 4px rgba(74,222,128,.12); }
        .tracking-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
        .tracking-metric { background:rgba(255,255,255,.06); border-radius:10px; padding:10px; min-width:0; }
        .tracking-metric small { display:block; color:#94a3b8; margin-bottom:4px; }
        .tracking-metric strong { color:#f8fafc; font-size:14px; overflow-wrap:anywhere; }
        .map-pin { width:40px; height:48px; filter:drop-shadow(0 5px 7px rgba(15,23,42,.35)); }
        .map-pin svg { display:block; width:100%; height:100%; }
        .vehicle-marker { width:48px; height:48px; display:grid; place-items:center; border-radius:50%; background:linear-gradient(145deg,#0ea5e9,#0369a1); border:3px solid #fff; box-shadow:0 6px 18px rgba(2,132,199,.45); color:#fff; }
        .vehicle-marker svg { width:26px; height:26px; fill:currentColor; }
        .map-legend { background:rgba(15,23,42,.92); color:#f8fafc; padding:9px 12px; border:1px solid rgba(255,255,255,.16); border-radius:10px; box-shadow:0 5px 18px rgba(0,0,0,.24); font:600 12px/1.35 'Segoe UI',sans-serif; }
        .map-legend div { display:flex; align-items:center; gap:7px; }
        .map-legend div+div { margin-top:5px; }
        .legend-dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
        .legend-pickup { background:#ef4444; }
        .legend-destination { background:#22c55e; }
        .legend-driver { background:#0ea5e9; }
        @media(max-width:600px) { .tracking-grid { grid-template-columns:1fr 1fr; } #map { height:52vh; min-height:360px; } }    </style>
    @include('partials.pwa-head')
</head>
<body>
    <div class="navbar">
        <h3>🚕 Detalles del Viaje</h3>
        <a href="{{ route('dashboard.' . $user['role']) }}" class="back-link">← Volver</a>
    </div>

    <div class="container">
        <div class="left-panel">
            @if(session('success'))
                <div class="success">✓ {{ session('success') }}</div>
            @endif

            <!-- Status Badge -->
            <div>
                @if($trip->status === 'pending')
                    <span class="status-badge status-pending">⏳ Esperando conductor</span>
                @elseif($trip->status === 'accepted')
                    <span class="status-badge status-accepted">🚗 Conductor aceptó</span>
                @elseif($trip->status === 'in_progress')
                    <span class="status-badge status-in-progress">📍 En viaje</span>
                @elseif($trip->status === 'completed')
                    <span class="status-badge status-completed">✓ Completado</span>
                @endif
            </div>

            <!-- Ubicaciones -->
            <div class="card">
                <div class="card-title">Ubicaciones</div>
                
                <div class="location-item">
                    <div class="location-icon">📍</div>
                    <div class="location-text">
                        <h4>Punto de recogida</h4>
                        <p>{{ $trip->origin }}</p>
                    </div>
                </div>

                <div class="location-item">
                    <div class="location-icon">🎯</div>
                    <div class="location-text">
                        <h4>Destino</h4>
                        <p>{{ $trip->destination }}</p>
                    </div>
                </div>
            </div>

            <!-- Información del viaje -->
            <div class="card">
                <div class="card-title">Información del viaje</div>
                
                <div class="detail-row">
                    <span class="detail-label">Identificador</span>
                    <span class="detail-value">#{{ $trip->id }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Costo estimado</span>
                    <span class="detail-value">{{ $trip->currency ?? 'COP' }} ${{ number_format($trip->estimated_cost, 0, ',', '.') }}</span>
                </div>

                @if($trip->distance_km)
                    <div class="detail-row">
                        <span class="detail-label">Distancia de ruta</span>
                        <span class="detail-value">{{ number_format($trip->distance_km, 2) }} km</span>
                    </div>
                @endif

                @if($trip->estimated_duration_minutes)
                    <div class="detail-row">
                        <span class="detail-label">Duracion estimada</span>
                        <span class="detail-value">{{ $trip->estimated_duration_minutes }} min</span>
                    </div>
                @endif

                @if($trip->base_fare !== null)
                    <div class="detail-row">
                        <span class="detail-label">Base + distancia + tiempo + servicio</span>
                        <span class="detail-value">${{ number_format($trip->base_fare + $trip->distance_fare + $trip->time_fare + $trip->booking_fee, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label">Tipo de viaje</span>
                    <span class="detail-value">{{ ucfirst($trip->vehicle_type ?? 'No definido') }}</span>
                </div>

                @if($trip->final_cost)
                    <div class="detail-row">
                        <span class="detail-label">Costo final</span>
                        <span class="detail-value">${{ number_format($trip->final_cost, 2) }}</span>
                    </div>
                @endif

                @if($trip->notes)
                    <div class="detail-row" style="flex-direction: column;">
                        <span class="detail-label" style="margin-bottom: 8px;">Notas</span>
                        <span class="detail-value">{{ $trip->notes }}</span>
                    </div>
                @endif
            </div>

            <!-- Información del conductor (si está asignado) -->
            @if($trip->driver)
                <div class="card">
                    <div class="card-title">Conductor</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Nombre</span>
                        <span class="detail-value">{{ $trip->driver->name }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Correo</span>
                        <span class="detail-value">{{ $trip->driver->email }}</span>
                    </div>
                    @if($trip->driver->driverProfile)
                        <div class="detail-row"><span class="detail-label">Vehículo</span><span class="detail-value">{{ trim(implode(' ', array_filter([$trip->driver->driverProfile->vehicle_color, $trip->driver->driverProfile->vehicle_make, $trip->driver->driverProfile->vehicle_model]))) ?: ucfirst($trip->vehicle_type) }}</span></div>
                        <div class="detail-row"><span class="detail-label">Placa</span><span class="detail-value">{{ strtoupper($trip->driver->driverProfile->vehicle_plate) }}</span></div>
                    @endif
                </div>
            @endif

            @if($trip->status === 'accepted' && $user['role'] === 'pasajero')
                <div class="waiting-message">
                    <p>PIN para iniciar el viaje</p>
                    <p style="font-size: 32px; font-weight: bold; color: #38bdf8; letter-spacing: 8px;">{{ $trip->pickup_pin }}</p>
                    <p>Comparte este PIN solamente cuando estes dentro del vehiculo correcto.</p>
                </div>
            @endif

            <!-- Estado del viaje -->
            @if($trip->status === 'pending' && $user['role'] === 'pasajero')
                <div class="waiting-message">
                    <p><span class="spinner">⏳</span></p>
                    <p>Esperando que un conductor acepte tu viaje...</p>
                </div>
            @elseif($trip->status === 'accepted' && $user['role'] === 'conductor')
                <div class="action-buttons">
                    <form method="POST" action="{{ route('trip.start', $trip->id) }}" style="width: 100%;">
                        @csrf
                        <label for="pickup_pin" style="display:block; margin-bottom:8px;">PIN entregado por el pasajero</label>
                        <input id="pickup_pin" name="pickup_pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required
                               style="width:100%; padding:12px; margin-bottom:10px; border-radius:8px; border:none; text-align:center; font-size:22px; letter-spacing:6px;">
                        @error('pickup_pin')<div style="color:#fecaca; margin-bottom:10px;">{{ $message }}</div>@enderror
                        <button type="submit" class="btn btn-primary">Iniciar viaje</button>
                    </form>
                </div>
            @elseif($trip->status === 'in_progress' && $user['role'] === 'conductor')
                <div class="action-buttons">
                    <form method="POST" action="{{ route('trip.complete', $trip->id) }}" style="width: 100%;">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            ✓ Completar viaje
                        </button>
                    </form>
                </div>
            @elseif($trip->status === 'pending' && $user['role'] === 'conductor')
                <div class="action-buttons">
                    <form method="POST" action="{{ route('trip.accept', $trip->id) }}" style="width: 100%;">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            ✓ Aceptar viaje
                        </button>
                    </form>
                </div>
            @elseif($trip->status === 'completed')
                @if($user['role'] === 'pasajero')
                    @php
                        $payment = $trip->payment;
                        $hasPayment = $payment && $payment->status === 'completed';
                    @endphp

                    @if($hasPayment)
                        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 15px; border-radius: 8px; color: #a7f3d0; text-align: center; margin-bottom: 20px;">
                            <p>✓ Pago completado</p>
                            <p style="font-size: 12px; color: #94a3b8; margin-top: 5px;">Monto: ${{ number_format($payment->amount, 2) }}</p>
                        </div>
                    @else
                        <div class="action-buttons">
                            <a href="{{ route('payment.show', $trip->id) }}" class="btn btn-primary" style="text-align: center; display: inline-block; width: 100%; padding: 12px 20px; text-decoration: none;">
                                💳 Procesar Pago
                            </a>
                        </div>
                    @endif
                @else
                    <div class="waiting-message">
                        <p>✓ Viaje completado</p>
                        <p style="font-size: 12px; color: #94a3b8;">Esperando que el pasajero realice el pago</p>
                    </div>
                @endif
            @endif
            @if(in_array($trip->status, ['pending', 'accepted', 'in_progress'], true))
                <div class="card">
                    <div class="card-title">Seguridad del viaje</div>
                    @if(session('share_url'))
                        <p style="word-break:break-all; margin-bottom:12px;">Enlace temporal: <a style="color:#7dd3fc" href="{{ session('share_url') }}">{{ session('share_url') }}</a></p>
                    @endif
                    <form method="POST" action="{{ route('trip.share',$trip->id) }}">@csrf<button class="btn btn-secondary" type="submit">Crear enlace para compartir</button></form>
                    <form method="POST" action="{{ route('trip.share.revoke',$trip->id) }}" style="margin-top:8px">@csrf @method('DELETE')<button class="btn btn-secondary" type="submit">Revocar enlaces</button></form>
                    <form method="POST" action="{{ route('trip.sos',$trip->id) }}" class="sos-form" style="margin-top:14px">@csrf<input type="hidden" name="latitude" class="sos-lat"><input type="hidden" name="longitude" class="sos-lng"><button type="submit" style="width:100%;padding:15px;border:0;border-radius:8px;background:#dc2626;color:white;font-weight:bold;">SOS — Necesito ayuda</button></form>
                    <p style="font-size:12px;color:#fca5a5;margin-top:8px;">Si existe peligro inmediato, contacta tambien a la linea local de emergencias.</p>
                </div>
            @endif

            <div class="card">
                <div class="card-title">Reportar un problema</div>
                <form method="POST" action="{{ route('trip.incident.report',$trip->id) }}">@csrf<select name="type" required style="width:100%;padding:10px;margin-bottom:8px"><option value="safety">Seguridad</option><option value="conduct">Conducta</option><option value="vehicle">Vehiculo</option><option value="lost_item">Objeto perdido</option><option value="other">Otro</option></select><textarea name="description" minlength="10" maxlength="2000" required style="width:100%;min-height:70px" placeholder="Describe lo ocurrido"></textarea><button class="btn btn-secondary" type="submit">Enviar reporte</button></form>
            </div>
            @if($trip->status === 'completed')
                @php
                    $alreadyRated = $trip->ratings()->where('reviewer_id', auth()->id())->exists();
                @endphp
                @if(!$alreadyRated)
                    <div class="card">
                        <div class="card-title">Califica este viaje</div>
                        <form method="POST" action="{{ route('trip.rating.store', $trip->id) }}">
                            @csrf
                            <select name="rating" required style="width:100%; padding:12px; border-radius:8px; margin-bottom:10px;">
                                <option value="">Selecciona una calificacion</option>
                                <option value="5">5 - Excelente</option><option value="4">4 - Muy bueno</option>
                                <option value="3">3 - Bueno</option><option value="2">2 - Regular</option><option value="1">1 - Malo</option>
                            </select>
                            <textarea name="comment" maxlength="500" placeholder="Comentario opcional" style="width:100%; min-height:70px; padding:12px; border-radius:8px; margin-bottom:10px;"></textarea>
                            <button class="btn btn-primary" type="submit">Enviar calificacion</button>
                        </form>
                    </div>
                @else
                    <div class="waiting-message"><p>Ya calificaste este viaje.</p></div>
                @endif
            @endif
            @if(in_array($trip->status, ['pending', 'accepted'], true))
                <div class="card">
                    <div class="card-title">Cancelar viaje</div>
                    <form method="POST" action="{{ route('trip.cancel', $trip->id) }}">
                        @csrf
                        <textarea name="reason" minlength="5" maxlength="500" required placeholder="Indica el motivo de cancelacion"
                                  style="width:100%; min-height:80px; padding:12px; border-radius:8px; margin-bottom:10px;"></textarea>
                        <button type="submit" class="btn btn-secondary" style="width:100%;">Cancelar viaje</button>
                    </form>
                </div>
            @elseif($trip->status === 'cancelled')
                <div class="waiting-message">
                    <p>Cancelado por: {{ $trip->cancelled_by }}</p>
                    <p>{{ $trip->cancellation_reason }}</p>
                </div>
            @endif
        </div>

        <!-- Right Panel - Map -->
        <div class="right-panel">
            <div id="map"></div>
            <div class="tracking-card" id="tracking-card" aria-live="polite">
                <div class="tracking-header">
                    <strong>Seguimiento del vehículo</strong>
                    <span class="tracking-status" id="tracking-status"><span class="tracking-dot"></span><span>Esperando señal</span></span>
                </div>
                <div id="automatic-safety-alert" style="display:none;background:#7f1d1d;color:#fee2e2;padding:11px;border-radius:9px;margin-bottom:10px;font-weight:700;"></div>
                <div class="tracking-grid">
                    <div class="tracking-metric"><small>Vehículo</small><strong id="tracking-vehicle">Sin asignar</strong></div>
                    <div class="tracking-metric"><small id="tracking-distance-label">Distancia hasta recogerte</small><strong id="tracking-distance">No disponible</strong></div>
                    <div class="tracking-metric"><small id="tracking-eta-label">Llegada a recogida</small><strong id="tracking-eta">No disponible</strong></div>
                    <div class="tracking-metric"><small>Última señal</small><strong id="tracking-updated">Esperando</strong></div>
                    <div class="tracking-metric"><small>Precisión GPS</small><strong id="tracking-accuracy">No disponible</strong></div>
                    <div class="tracking-metric"><small>Conexión</small><strong id="tracking-connection">Consultando...</strong></div>
                </div>
            </div>

            @if($user['role'] === 'conductor' && in_array($trip->status, ['accepted', 'in_progress'], true))
                <button type="button" id="focus-navigation-target" class="btn btn-primary" style="margin-top:14px;width:100%;">
                    {{ $trip->status === 'in_progress' ? 'Ver destino en el mapa' : 'Ver punto de recogida en el mapa' }}
                </button>
            @endif
            <!-- Map Info -->
            @if($trip->origin_lat && $trip->destination_lat)
                <div class="map-info">
                    <div class="map-info-row">
                        <span class="map-info-label">📍 Origen</span>
                        <span class="map-info-value" style="text-align:right;max-width:68%;">{{ $trip->origin }}</span>
                    </div>
                    <div class="map-info-row">
                        <span class="map-info-label">🎯 Destino</span>
                        <span class="map-info-value" style="text-align:right;max-width:68%;">{{ $trip->destination }}</span>
                    </div>
                    <div class="map-info-row">
                        <span class="map-info-label">🚍 Modo de transporte</span>
                        <span class="map-info-value">
                            <select id="transportMode" style="background: transparent; color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 6px; padding: 4px 8px;">
                                <option value="driving">Carro</option>
                                <option value="walking">A pie</option>
                            </select>
                        </span>
                    </div>
                    <div class="map-info-row">
                        <span class="map-info-label">📏 Distancia en ruta</span>
                        <span class="map-info-value" id="routeDistance">Cargando...</span>
                    </div>
                    <div class="map-info-row">
                        <span class="map-info-label">🚶 Tiempo a pie</span>
                        <span class="map-info-value" id="walkingTime">Cargando...</span>
                    </div>
                    <div class="map-info-row">
                        <span class="map-info-label">🚗 Tiempo en carro</span>
                        <span class="map-info-value" id="drivingTime">Cargando...</span>
                    </div>
                    <div class="map-info-row">
                        <span class="map-info-label">🧭 Ruta activa</span>
                        <span class="map-info-value" id="activeMode">Carro</span>
                    </div>
                    <div class="map-info-row" style="flex-direction: column; gap: 8px;">
                        <span id="routeStatus" style="font-size: 12px; color: #f8c291;">Usando rutas de calles reales desde OpenStreetMap.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        @php
            $tripMapData = [
                'id' => $trip->id,
                'status' => $trip->status,
                'role' => $user['role'],
                'userName' => $user['name'],
                'vehicleType' => $trip->vehicle_type,
                'originLat' => (float) ($trip->origin_lat ?? 0),
                'originLng' => (float) ($trip->origin_lng ?? 0),
                'destLat' => (float) ($trip->destination_lat ?? 0),
                'destLng' => (float) ($trip->destination_lng ?? 0),
                'origin' => $trip->origin,
                'destination' => $trip->destination,
                'municipality' => $trip->municipality,
                'department' => $trip->department,
            ];
        @endphp
        const tripData = {{ Illuminate\Support\Js::from($tripMapData) }};
        const liveStatusUrl = @json(route('trip.live-status', $trip->id));
        const driverLocationUrl = @json(route('driver.location'));
        const csrfToken = @json(csrf_token());
        const map = L.map('map', { zoomControl: true }).setView(
            tripData.originLat ? [tripData.originLat, tripData.originLng] : [4.7110, -74.0055], 14
        );
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors', maxZoom: 19
        }).addTo(map);

        const ui = {
            status: document.getElementById('tracking-status'), vehicle: document.getElementById('tracking-vehicle'),
            distance: document.getElementById('tracking-distance'), eta: document.getElementById('tracking-eta'),
            distanceLabel: document.getElementById('tracking-distance-label'), etaLabel: document.getElementById('tracking-eta-label'),
            updated: document.getElementById('tracking-updated'), accuracy: document.getElementById('tracking-accuracy'),
            connection: document.getElementById('tracking-connection')
        };
        const transportModeSelect = document.getElementById('transportMode');
        const routeDistanceEl = document.getElementById('routeDistance');
        const walkingTimeEl = document.getElementById('walkingTime');
        const drivingTimeEl = document.getElementById('drivingTime');
        const activeModeEl = document.getElementById('activeMode');
        const routeStatusEl = document.getElementById('routeStatus');
        let routeLayer, approachLayer, driverMarker, accuracyCircle, lastDriverPosition, animationFrame;

        const pickupIcon = () => L.divIcon({className:'', html:`<div class="map-pin" aria-label="Punto de recogida"><svg viewBox="0 0 40 48" aria-hidden="true"><path fill="#ef4444" stroke="#fff" stroke-width="3" d="M20 2C10.6 2 3 9.6 3 19c0 12.1 17 27 17 27s17-14.9 17-27C37 9.6 29.4 2 20 2z"/><circle cx="20" cy="18" r="7" fill="#fff"/><circle cx="20" cy="16" r="2.5" fill="#ef4444"/><path d="M14.8 23c.9-3.2 3-4.8 5.2-4.8s4.3 1.6 5.2 4.8" fill="none" stroke="#ef4444" stroke-width="2.2" stroke-linecap="round"/></svg></div>`,iconSize:[40,48],iconAnchor:[20,46],popupAnchor:[0,-42]});
        const destinationIcon = () => L.divIcon({className:'', html:`<div class="map-pin" aria-label="Destino"><svg viewBox="0 0 40 48" aria-hidden="true"><path fill="#22c55e" stroke="#fff" stroke-width="3" d="M20 2C10.6 2 3 9.6 3 19c0 12.1 17 27 17 27s17-14.9 17-27C37 9.6 29.4 2 20 2z"/><path d="M14 26V11m1 1h12l-3 4 3 4H15" fill="none" stroke="#fff" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round"/></svg></div>`,iconSize:[40,48],iconAnchor:[20,46],popupAnchor:[0,-42]});
        const vehicleIcon = type => { const shape=type==='moto'?`<svg viewBox="0 0 24 24"><path d="M5.5 18.5a3.5 3.5 0 1 1 2.9-5.45l1.2-1.8-1.8-2.7H5.5V6.5h3.4l1.2 1.8h3.7l1.1-2.2H13V4h4.8v2.1h-.6l-1 2 2.1 4.7a3.5 3.5 0 1 1-1.9.8l-.5-1.1H11l-.9 1.4a3.5 3.5 0 0 1-4.6 4.6Zm13 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm-13 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm5.8-8 1.5-2.2h1.7l1 2.2h-4.2Z"/></svg>`:`<svg viewBox="0 0 24 24"><path d="M5.2 6.5A2.5 2.5 0 0 1 7.5 5h9a2.5 2.5 0 0 1 2.3 1.5L21 12v6h-2v1.5h-2V18H7v1.5H5V18H3v-6l2.2-5.5ZM7.5 7a.5.5 0 0 0-.46.3L5.56 11h12.88l-1.48-3.7a.5.5 0 0 0-.46-.3h-9ZM6.5 16a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm11 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/></svg>`; return L.divIcon({className:'',html:`<div class="vehicle-marker" aria-label="${type==='moto'?'Moto':'Carro'} del conductor">${shape}</div>`,iconSize:[48,48],iconAnchor:[24,24],popupAnchor:[0,-25]}); };
        const originMarker = tripData.originLat ? L.marker([tripData.originLat,tripData.originLng], {icon:pickupIcon()}).addTo(map).bindPopup(`<b>Te recogen aquí</b><br>${escapeHtml(tripData.origin)}`) : null;
        const destMarker = tripData.destLat ? L.marker([tripData.destLat,tripData.destLng], {icon:destinationIcon()}).addTo(map).bindPopup(`<b>Tu destino</b><br>${escapeHtml(tripData.destination)}`) : null;
        const legend=L.control({position:'bottomleft'}); legend.onAdd=()=>{const el=L.DomUtil.create('div','map-legend'); el.innerHTML='<div><span class="legend-dot legend-driver"></span>Tu conductor</div><div><span class="legend-dot legend-pickup"></span>Te recogen aquí</div><div><span class="legend-dot legend-destination"></span>Tu destino</div>'; return el;}; legend.addTo(map);

        function escapeHtml(value) { const e=document.createElement('div'); e.textContent=value || ''; return e.innerHTML; }
        function formatDuration(seconds) { if (!seconds) return 'No disponible'; const m=Math.max(1,Math.round(seconds/60)); return m<60?`${m} min`:`${Math.floor(m/60)} h ${m%60} min`; }
        function formatDistance(meters) { return meters ? `${(meters/1000).toFixed(2)} km` : 'No disponible'; }
        function relativeTime(iso, age) { if (!iso && age == null) return 'Sin señal'; const seconds=Math.max(0, age ?? Math.round((Date.now()-new Date(iso).getTime())/1000)); return seconds<10?'Ahora mismo':seconds<60?`Hace ${seconds} s`:`Hace ${Math.floor(seconds/60)} min`; }
        function setTrackingState(driver, source='consulta') {
            const fresh=Boolean(driver?.location_fresh && driver.latitude != null && driver.longitude != null);
            ui.status.className='tracking-status '+(fresh?'live':'stale');
            ui.status.lastElementChild.textContent=fresh?'Ubicación en vivo':driver?'Señal no disponible':'Esperando conductor';
            ui.connection.textContent=source==='realtime'?'Tiempo real':'Respaldo automático';
            ui.updated.textContent=relativeTime(driver?.location_updated_at, driver?.location_age_seconds);
            ui.accuracy.textContent=driver?.accuracy ? `± ${Math.round(driver.accuracy)} m` : 'No disponible';
            const distance=driver?.distance_to_target_km ?? driver?.distance_to_pickup_km;
            const eta=driver?.eta_to_target_minutes ?? driver?.eta_to_pickup_minutes;
            ui.distance.textContent=distance != null ? `${Number(distance).toFixed(2)} km` : 'No disponible';
            ui.eta.textContent=eta ? `${eta} min aprox.` : 'No disponible';
            const toDestination=driver?.trip_stage==='to_destination';
            ui.distanceLabel.textContent=toDestination?'Distancia al destino':'Distancia hasta recogerte';
            ui.etaLabel.textContent=toDestination?'Llegada al destino':'Llegada a recogida';
            const v=driver?.vehicle; ui.vehicle.textContent=v ? [v.color,v.make,v.model,v.plate].filter(Boolean).join(' · ') || (v.type==='moto'?'Moto':'Carro') : 'Sin asignar';
            if (fresh) updateDriverMarker(driver);
        }
        function smoothMove(marker, target) {
            if (!marker) return; if (animationFrame) cancelAnimationFrame(animationFrame);
            const from=marker.getLatLng(), started=performance.now(), duration=1200;
            const frame=now=>{const t=Math.min(1,(now-started)/duration), eased=1-Math.pow(1-t,3); marker.setLatLng([from.lat+(target[0]-from.lat)*eased,from.lng+(target[1]-from.lng)*eased]); if(t<1) animationFrame=requestAnimationFrame(frame);};
            animationFrame=requestAnimationFrame(frame);
        }
        function updateDriverMarker(driver) {
            const position=[Number(driver.latitude),Number(driver.longitude)];
            if (!driverMarker) driverMarker=L.marker(position,{icon:vehicleIcon(driver.vehicle?.type || tripData.vehicleType),zIndexOffset:1000}).addTo(map).bindPopup(`<b>${escapeHtml(driver.name || 'Conductor')}</b><br>Vehículo en camino`);
            else smoothMove(driverMarker,position);
            if (accuracyCircle) accuracyCircle.setLatLng(position).setRadius(Math.min(Number(driver.accuracy)||10,500));
            else accuracyCircle=L.circle(position,{radius:Math.min(Number(driver.accuracy)||10,500),color:'#38bdf8',weight:1,fillOpacity:.08,interactive:false}).addTo(map);
            lastDriverPosition=position;
            if (tripData.status==='accepted') drawApproach(position);
        }
        async function drawApproach(position) {
            try { const route=await requestRoute('driving',position,[tripData.originLat,tripData.originLng]); if(approachLayer)map.removeLayer(approachLayer); approachLayer=L.geoJSON(route.geometry,{style:{color:'#22c55e',weight:5,opacity:.85,dashArray:'8,7'}}).addTo(map); }
            catch (_) { if(approachLayer)map.removeLayer(approachLayer); approachLayer=L.polyline([position,[tripData.originLat,tripData.originLng]],{color:'#22c55e',dashArray:'8,7'}).addTo(map); }
        }
        async function requestRoute(profile, from=[tripData.originLat,tripData.originLng], to=[tripData.destLat,tripData.destLng]) {
            const url=`https://router.project-osrm.org/route/v1/${profile}/${from[1]},${from[0]};${to[1]},${to[0]}?overview=full&geometries=geojson&steps=false`;
            const response=await fetch(url); if(!response.ok)throw new Error('Ruta no disponible'); const data=await response.json(); if(!data.routes?.length)throw new Error('Ruta no disponible'); return data.routes[0];
        }
        async function updateRoute() {
            if(!tripData.originLat||!tripData.destLat)return;
            const mode=transportModeSelect?.value || 'driving';
            try { const [walking,driving]=await Promise.all([requestRoute('foot'),requestRoute('driving')]); const selected=mode==='walking'?walking:driving; if(routeLayer)map.removeLayer(routeLayer); routeLayer=L.geoJSON(selected.geometry,{style:{color:mode==='walking'?'#f97316':'#38bdf8',weight:5,opacity:.8}}).addTo(map); routeDistanceEl.textContent=formatDistance(selected.distance); walkingTimeEl.textContent=formatDuration(walking.duration); drivingTimeEl.textContent=formatDuration(driving.duration); activeModeEl.textContent=mode==='walking'?'A pie':'Carro'; routeStatusEl.textContent='Ruta calculada por calles reales.'; fitAll(); }
            catch(e) { routeStatusEl.textContent='Servicio de ruta no disponible; mostrando trayecto directo.'; routeLayer=L.polyline([[tripData.originLat,tripData.originLng],[tripData.destLat,tripData.destLng]],{color:'#38bdf8',dashArray:'6,6'}).addTo(map); fitAll(); }
        }
        function fitAll(){ const points=[]; if(originMarker)points.push(originMarker.getLatLng()); if(destMarker)points.push(destMarker.getLatLng()); if(lastDriverPosition)points.push(lastDriverPosition); if(points.length>1)map.fitBounds(L.latLngBounds(points),{padding:[45,45],maxZoom:16}); }
        async function refreshDriverLocation(){ if(document.hidden)return; try{const response=await fetch(liveStatusUrl,{headers:{Accept:'application/json'},cache:'no-store'}); if(!response.ok)throw new Error(); const data=await response.json(); if(data.status!==tripData.status){location.reload();return;} setTrackingState(data.driver,'polling'); const warning=document.getElementById('automatic-safety-alert'); if(data.safety_alerts?.length){warning.style.display='block';warning.textContent='Alerta de seguridad: '+data.safety_alerts[0].description.replace('[AUTOMATICA] ','');}else warning.style.display='none';}catch(e){ui.connection.textContent='Reconectando...';}}

        updateRoute(); transportModeSelect?.addEventListener('change',updateRoute);
        document.getElementById('focus-navigation-target')?.addEventListener('click',()=>{const target=tripData.status==='in_progress'?[tripData.destLat,tripData.destLng]:[tripData.originLat,tripData.originLng];map.flyTo(target,17,{duration:1});(tripData.status==='in_progress'?destMarker:originMarker)?.openPopup();});
        if (tripData.role==='conductor' && ['accepted','in_progress'].includes(tripData.status)) {
            let lastLocationSentAt=0;
            if (!navigator.geolocation) {
                ui.status.className='tracking-status stale';
                ui.status.lastElementChild.textContent='GPS no disponible';
                ui.connection.textContent='Este dispositivo no permite ubicación';
            } else {
                navigator.geolocation.watchPosition(async position=>{
                    const now=Date.now(); if(now-lastLocationSentAt<5000)return; lastLocationSentAt=now;
                    const coords=position.coords;
                    setTrackingState({name:tripData.userName,latitude:coords.latitude,longitude:coords.longitude,accuracy:coords.accuracy,location_fresh:true,location_updated_at:new Date().toISOString(),trip_stage:tripData.status==='in_progress'?'to_destination':'to_pickup',vehicle:{type:tripData.vehicleType}},'realtime');
                    ui.connection.textContent='Transmitiendo al pasajero';
                    try {
                        const response=await fetch(driverLocationUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},body:JSON.stringify({latitude:coords.latitude,longitude:coords.longitude,accuracy:coords.accuracy,heading:coords.heading,speed:coords.speed})});
                        if(!response.ok)throw new Error('No se pudo publicar la ubicación');
                    } catch(error) {
                        ui.connection.textContent='Sin conexión, reintentando...';
                        lastLocationSentAt=0;
                    }
                },error=>{
                    ui.status.className='tracking-status stale';
                    ui.status.lastElementChild.textContent='Ubicación desactivada';
                    ui.connection.textContent=error.code===1?'Permite la ubicación en el navegador':'Buscando señal GPS...';
                },{enableHighAccuracy:true,maximumAge:2000,timeout:12000});
            }
        }
        if (tripData.role==='pasajero' && ['pending','accepted','in_progress'].includes(tripData.status)) {
            refreshDriverLocation(); setInterval(refreshDriverLocation,7000); document.addEventListener('visibilitychange',()=>{if(!document.hidden)refreshDriverLocation();});
            const connect=()=>{if(!window.Echo)return setTimeout(connect,300); window.Echo.private(`trips.${tripData.id}`).listen('.realtime.update',event=>{if(event.type==='trip.updated')return location.reload(); if(event.type==='driver.location'){setTrackingState({...event.data,location_fresh:true},'realtime');}});}; connect();
        } else {
            ui.status.lastElementChild.textContent=tripData.status==='completed'?'Viaje finalizado':'Vista del conductor'; ui.connection.textContent='No aplica';
        }
    </script>
<script>document.querySelectorAll('.sos-form').forEach(form=>form.addEventListener('submit',function(e){if(this.dataset.ready)return;e.preventDefault();navigator.geolocation.getCurrentPosition(p=>{this.querySelector('.sos-lat').value=p.coords.latitude;this.querySelector('.sos-lng').value=p.coords.longitude;this.dataset.ready='1';this.submit();},()=>{this.dataset.ready='1';this.submit();},{enableHighAccuracy:true,timeout:5000});}));</script>    @include('partials.pwa-script')
</body>
</html>

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
    </style>
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
                    <span class="detail-value">${{ number_format($trip->estimated_cost, 2) }}</span>
                </div>

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
                        <span class="detail-label">Teléfono</span>
                        <span class="detail-value">{{ $trip->driver->email }}</span>
                    </div>
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
                        <button type="submit" class="btn btn-primary">
                            🚗 Iniciar viaje
                        </button>
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
        </div>

        <!-- Right Panel - Map -->
        <div class="right-panel">
            <div id="map"></div>

            <!-- Map Info -->
            @if($trip->origin_lat && $trip->destination_lat)
                <div class="map-info">
                    <div class="map-info-row">
                        <span class="map-info-label">📍 Origen</span>
                        <span class="map-info-value">{{ number_format($trip->origin_lat, 4) }}, {{ number_format($trip->origin_lng, 4) }}</span>
                    </div>
                    <div class="map-info-row">
                        <span class="map-info-label">🎯 Destino</span>
                        <span class="map-info-value">{{ number_format($trip->destination_lat, 4) }}, {{ number_format($trip->destination_lng, 4) }}</span>
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
        // Initialize map
        const map = L.map('map').setView([4.7110, -74.0055], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        // Origin and destination coordinates
        const tripData = {
            originLat: parseFloat('{{ $trip->origin_lat ?? 0 }}'),
            originLng: parseFloat('{{ $trip->origin_lng ?? 0 }}'),
            destLat: parseFloat('{{ $trip->destination_lat ?? 0 }}'),
            destLng: parseFloat('{{ $trip->destination_lng ?? 0 }}'),
            origin: '{{ addslashes($trip->origin) }}',
            destination: '{{ addslashes($trip->destination) }}'
        };

        const transportModeSelect = document.getElementById('transportMode');
        const routeDistanceEl = document.getElementById('routeDistance');
        const walkingTimeEl = document.getElementById('walkingTime');
        const drivingTimeEl = document.getElementById('drivingTime');
        const activeModeEl = document.getElementById('activeMode');
        const routeStatusEl = document.getElementById('routeStatus');

        let routeLayer = null;
        let originMarker = null;
        let destMarker = null;

        function formatDuration(seconds) {
            if (!seconds || seconds === 0) {
                return 'No disponible';
            }
            const minutes = Math.round(seconds / 60);
            if (minutes < 60) {
                return `${minutes} min`;
            }
            const hours = Math.floor(minutes / 60);
            const remainder = minutes % 60;
            return `${hours} h ${remainder} min`;
        }

        function formatDistance(meters) {
            if (!meters || meters === 0) {
                return 'No disponible';
            }
            return `${(meters / 1000).toFixed(2)} km`;
        }

        async function requestRoute(profile) {
            const url = `https://router.project-osrm.org/route/v1/${profile}/${tripData.originLng},${tripData.originLat};${tripData.destLng},${tripData.destLat}?overview=full&geometries=geojson&steps=false`;
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('No se pudo obtener la ruta');
            }
            const data = await response.json();
            if (!data.routes || !data.routes.length) {
                throw new Error('Ruta no disponible');
            }
            return data.routes[0];
        }

        async function updateRoute() {
            if (!tripData.originLat || !tripData.originLng || !tripData.destLat || !tripData.destLng) {
                routeStatusEl.textContent = 'Coordenadas de origen o destino no están disponibles.';
                return;
            }

            const selectedMode = transportModeSelect.value;
            activeModeEl.textContent = selectedMode === 'walking' ? 'A pie' : 'Carro';
            routeStatusEl.textContent = 'Calculando ruta con datos de calle reales...';

            try {
                const [walkingRoute, drivingRoute] = await Promise.all([
                    requestRoute('foot'),
                    requestRoute('driving')
                ]);

                walkingTimeEl.textContent = formatDuration(walkingRoute.duration);
                drivingTimeEl.textContent = formatDuration(drivingRoute.duration);
                routeDistanceEl.textContent = formatDistance(selectedMode === 'walking' ? walkingRoute.distance : drivingRoute.distance);

                const selectedRoute = selectedMode === 'walking' ? walkingRoute : drivingRoute;
                const routeColor = selectedMode === 'walking' ? '#f97316' : '#38bdf8';

                if (routeLayer) {
                    map.removeLayer(routeLayer);
                }

                routeLayer = L.geoJSON(selectedRoute.geometry, {
                    style: {
                        color: routeColor,
                        weight: 5,
                        opacity: 0.8
                    }
                }).addTo(map);

                if (originMarker) {
                    map.removeLayer(originMarker);
                }
                if (destMarker) {
                    map.removeLayer(destMarker);
                }

                originMarker = L.marker([tripData.originLat, tripData.originLng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map).bindPopup('<b>Punto de recogida</b><br>' + tripData.origin);

                destMarker = L.marker([tripData.destLat, tripData.destLng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map).bindPopup('<b>Destino</b><br>' + tripData.destination);

                const bounds = routeLayer.getBounds().extend(originMarker.getLatLng()).extend(destMarker.getLatLng());
                map.fitBounds(bounds, { padding: [50, 50] });
                routeStatusEl.textContent = 'Ruta calculada con calles reales.';
            } catch (error) {
                routeStatusEl.textContent = 'No fue posible obtener la ruta real. Se muestra línea directa.';
                console.error(error);

                if (routeLayer) {
                    map.removeLayer(routeLayer);
                }
                routeLayer = L.polyline([
                    [tripData.originLat, tripData.originLng],
                    [tripData.destLat, tripData.destLng]
                ], {
                    color: '#38bdf8',
                    weight: 3,
                    opacity: 0.7,
                    dashArray: '5, 5'
                }).addTo(map);

                if (originMarker) {
                    map.removeLayer(originMarker);
                }
                if (destMarker) {
                    map.removeLayer(destMarker);
                }

                originMarker = L.marker([tripData.originLat, tripData.originLng]).addTo(map).bindPopup('<b>Punto de recogida</b><br>' + tripData.origin);
                destMarker = L.marker([tripData.destLat, tripData.destLng]).addTo(map).bindPopup('<b>Destino</b><br>' + tripData.destination);

                const bounds = L.latLngBounds([
                    [tripData.originLat, tripData.originLng],
                    [tripData.destLat, tripData.destLng]
                ]);
                map.fitBounds(bounds, { padding: [50, 50] });
                walkingTimeEl.textContent = 'No disponible';
                drivingTimeEl.textContent = 'No disponible';
                routeDistanceEl.textContent = 'No disponible';
            }
        }

        if (tripData.originLat && tripData.originLng && tripData.destLat && tripData.destLng) {
            updateRoute();
            transportModeSelect.addEventListener('change', updateRoute);
        } else {
            routeStatusEl.textContent = 'No hay coordenadas válidas para mostrar la ruta.';
            routeDistanceEl.textContent = 'No disponible';
            walkingTimeEl.textContent = 'No disponible';
            drivingTimeEl.textContent = 'No disponible';
        }
    </script>
</body>
</html>

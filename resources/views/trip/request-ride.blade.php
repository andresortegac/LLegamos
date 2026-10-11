<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Viaje | Llegamos</title>
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
            max-width: 980px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 15px;
            padding: 28px;
        }

        .title {
            color: #38bdf8;
            margin-bottom: 18px;
            font-size: 24px;
        }

        .step {
            margin-bottom: 18px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .step:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .step strong {
            display: block;
            margin-bottom: 10px;
            color: #7dd3fc;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #e2e8f0;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            background: rgba(255,255,255,0.05);
            color: white;
            font-size: 14px;
        }

        .form-group select option {
            color: #0f172a;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #38bdf8;
            background: rgba(56, 189, 248, 0.1);
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .hint {
            margin-top: 6px;
            font-size: 12px;
            color: #94a3b8;
        }

        .required {
            color: #ef4444;
        }

        .error {
            background: rgba(239, 68, 68, 0.2);
            color: #fecaca;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .error ul {
            list-style: none;
            margin-top: 8px;
        }

        .error li {
            margin-bottom: 6px;
        }

        #map {
            height: 440px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.2);
            display: none;
        }

        .map-info {
            margin-top: 12px;
            font-size: 13px;
            color: #cbd5e1;
            display: none;
        }

        .map-info p {
            margin-bottom: 8px;
        }

        .cost-display {
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 8px;
            text-align: center;
            padding: 14px;
            margin: 18px 0;
        }

        .cost-value {
            color: #10b981;
            font-size: 28px;
            font-weight: bold;
            margin: 6px 0;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.2s;
        }

        .btn-location {
            margin-top: 8px;
            background: #22c55e;
            color: #052e16;
            width: 100%;
            padding: 10px 12px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-location:hover { background: #16a34a; }
        .location-status { margin-top:8px; padding:9px 11px; border-radius:8px; background:#0f172a; color:#bfdbfe; }
        .location-status.success { background:#064e3b; color:#d1fae5; }
        .location-status.error { background:#7f1d1d; color:#fee2e2; }

        .btn-primary {
            background: #38bdf8;
            color: #0f172a;
        }

        .btn-primary:hover {
            background: #0ea5e9;
        }

        .btn-cancel {
            margin-top: 10px;
            background: #64748b;
            color: white;
        }

        .btn-cancel:hover {
            background: #475569;
        }

        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }

            #map {
                height: 360px;
            }
        }
    </style>
    @include('partials.pwa-head')
</head>
<body>
    <div class="navbar">
        <h3>Solicitar Viaje</h3>
        <a href="{{ route('dashboard.pasajero') }}" class="back-link">Volver al panel</a>
    </div>

    <div class="container">
        <div class="card">
            <h2 class="title">Detalles del viaje</h2>

            @if($errors->any())
                <div class="error">
                    <strong>Revisa los siguientes errores:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('trip.store-request') }}">
                @csrf

                <div class="step">
                    <strong>Paso 1: Departamento</strong>
                    <div class="form-group">
                        <label for="department">Departamento <span class="required">*</span></label>
                        <select name="department" id="department" required>
                            <option value="">Selecciona un departamento</option>
                        </select>
                        <p class="hint" id="departments-loading">Cargando departamentos...</p>
                    </div>
                </div>

                <div class="step">
                    <strong>Paso 2: Municipio</strong>
                    <div class="form-group">
                        <label for="municipality">Municipio <span class="required">*</span></label>
                        <select name="municipality" id="municipality" required disabled>
                            <option value="">Selecciona un municipio</option>
                        </select>
                        <p class="hint">Los municipios se cargan segun el departamento.</p>
                    </div>
                </div>

                <div class="step">
                    <strong>Paso 3: Tipo de viaje</strong>
                    <div class="form-group">
                        <label for="vehicle_type">Vehiculo que prefieres <span class="required">*</span></label>
                        <select name="vehicle_type" id="vehicle_type" required>
                            <option value="">Selecciona una opcion</option>
                            <option value="carro" {{ old('vehicle_type') === 'carro' ? 'selected' : '' }}>Carro</option>
                            <option value="moto" {{ old('vehicle_type') === 'moto' ? 'selected' : '' }}>Moto</option>
                        </select>
                        <p class="hint">Esto ayuda a asignarte el tipo de servicio adecuado.</p>
                    </div>
                </div>

                <div class="step" id="address-section">
                    <strong>Paso 5: Direcciones</strong>
                    <div class="form-group">
                        <label for="origin">Punto de partida <span class="required">*</span></label>
                        <input
                            type="text"
                            name="origin"
                            id="origin"
                            value="{{ old('origin') }}"
                            placeholder="Ej: Calle 10 #20-30"
                            required
                            disabled
                        >
                        <button type="button" id="share-location-btn" class="btn-location">
                            Compartir mi ubicacion
                        </button>
                        <p id="location-status" class="location-status" role="status" aria-live="polite">Puedes escribir la direccion o usar la ubicacion del dispositivo.</p>
                    </div>

                    <div class="form-group">
                        <label for="destination">¿A donde vas? <span class="required">*</span></label>
                        <input
                            type="text"
                            name="destination"
                            id="destination"
                            value="{{ old('destination') }}"
                            placeholder="Ej: Carrera 50 #12-10"
                            required
                            disabled
                        >
                        <p class="hint">Si haces clic en el mapa, guardamos coordenadas de apoyo.</p>
                    </div>

                    <input type="hidden" name="origin_lat" id="origin_lat" value="{{ old('origin_lat') }}">
                    <input type="hidden" name="origin_lng" id="origin_lng" value="{{ old('origin_lng') }}">
                    <input type="hidden" name="destination_lat" id="destination_lat" value="{{ old('destination_lat') }}">
                    <input type="hidden" name="destination_lng" id="destination_lng" value="{{ old('destination_lng') }}">

                    <div class="form-group">
                        <label for="notes">Notas adicionales (opcional)</label>
                        <textarea name="notes" id="notes" placeholder="Ej: Llevo equipaje">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="cost-display">
                    <p>Costo estimado</p>
                    <div class="cost-value">$18 - $50</div>
                    <p style="font-size: 12px;">El precio depende de la distancia.</p>
                </div>

                <button type="submit" class="btn btn-primary">Solicitar viaje</button>
                <button type="button" class="btn btn-cancel" onclick="history.back()">Cancelar</button>
            </form>
        </div>

        <div class="card">
            <h2 class="title">Paso 4: Mapa del municipio</h2>
            <div id="map"></div>
            <div id="map-placeholder" class="hint">
                Comparte tu ubicacion para abrir el mapa automaticamente o selecciona departamento y municipio.
            </div>
            <div class="map-info" id="map-info">
                <p><strong>Recogida:</strong> <span id="origin-display">No seleccionado</span></p>
                <p><strong>Llegada:</strong> <span id="destination-display">No seleccionado</span></p>
                <p id="map-action-status"><strong>Instruccion:</strong> comparte tu ubicacion y luego toca en el mapa el lugar a donde quieres ir.</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        const departmentsUrl = @json(route('locations.departments'));
        const municipalitiesUrlTemplate = @json(route('locations.municipalities', ['departmentId' => '__ID__']));
        const departmentSelect = document.getElementById('department');
        const municipalitySelect = document.getElementById('municipality');
        const mapElement = document.getElementById('map');
        const mapInfo = document.getElementById('map-info');
        const mapPlaceholder = document.getElementById('map-placeholder');
        const departmentsLoading = document.getElementById('departments-loading');
        const originInput = document.getElementById('origin');
        const destinationInput = document.getElementById('destination');
        const shareLocationBtn = document.getElementById('share-location-btn');
        const locationStatus = document.getElementById('location-status');

        let departments = [];
        let municipalitiesByDepartment = {};
        let map = null;
        let originMarker = null;
        let destinationMarker = null;
        let selectionMode = 'origin';

        const oldDepartment = @json(old('department'));
        const oldMunicipality = @json(old('municipality'));
        const oldOriginLat = @json(old('origin_lat'));
        const oldOriginLng = @json(old('origin_lng'));
        const oldDestinationLat = @json(old('destination_lat'));
        const oldDestinationLng = @json(old('destination_lng'));

        function buildMunicipalitiesUrl(departmentId) {
            return municipalitiesUrlTemplate.replace('__ID__', String(departmentId));
        }

        async function loadDepartments(keepDepartment = null, keepMunicipality = null) {
            departmentSelect.innerHTML = '<option value="">Selecciona un departamento</option>';

            try {
                const response = await fetch(departmentsUrl);
                if (!response.ok) {
                    throw new Error('No se pudo cargar el catalogo de departamentos.');
                }

                departments = await response.json();
                departments.forEach((department) => {
                    const option = document.createElement('option');
                    option.value = department.name;
                    option.textContent = department.name;
                    option.dataset.id = department.id;

                    if (keepDepartment && keepDepartment === department.name) {
                        option.selected = true;
                    }

                    departmentSelect.appendChild(option);
                });

                departmentsLoading.textContent = 'Departamentos cargados.';

                if (keepDepartment) {
                    const departmentId = getDepartmentIdByName(keepDepartment);
                    if (departmentId) {
                        await fillMunicipalities(departmentId, keepMunicipality);
                    }
                }
            } catch (error) {
                departmentsLoading.textContent = 'No se pudo cargar el listado completo.';
            }
        }

        function getDepartmentIdByName(departmentName) {
            const department = departments.find((item) => item.name === departmentName);
            return department ? department.id : null;
        }

        async function fillMunicipalities(departmentId, keepValue = null) {
            municipalitySelect.innerHTML = '<option value="">Selecciona un municipio</option>';

            if (!departmentId) {
                municipalitySelect.disabled = true;
                return;
            }

            municipalitySelect.disabled = false;
            municipalitySelect.innerHTML = '<option value="">Cargando municipios...</option>';

            if (!municipalitiesByDepartment[departmentId]) {
                const response = await fetch(buildMunicipalitiesUrl(departmentId));
                if (!response.ok) {
                    municipalitySelect.innerHTML = '<option value="">No se pudieron cargar municipios</option>';
                    municipalitySelect.disabled = true;
                    return;
                }

                municipalitiesByDepartment[departmentId] = await response.json();
            }

            const municipalities = municipalitiesByDepartment[departmentId];
            municipalitySelect.innerHTML = '<option value="">Selecciona un municipio</option>';

            municipalities.forEach((municipality) => {
                const option = document.createElement('option');
                option.value = municipality.name;
                option.textContent = municipality.name;
                if (keepValue && keepValue === municipality.name) {
                    option.selected = true;
                }
                municipalitySelect.appendChild(option);
            });
        }

        function enableAddressInputs(enabled) {
            originInput.disabled = !enabled;
            destinationInput.disabled = !enabled;
        }

        function normalizePlace(value) {
            return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
        }

        function ensureMap(lat, lng, zoom = 13) {
            if (typeof L === 'undefined') {
                locationStatus.textContent = 'No se pudo cargar el mapa, pero conservaremos las coordenadas GPS.';
                locationStatus.classList.add('error');
                return false;
            }
            mapElement.style.display = 'block';
            mapInfo.style.display = 'block';
            mapPlaceholder.style.display = 'none';
            if (!map) {
                map = L.map('map').setView([lat, lng], zoom);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: 'OpenStreetMap contributors', maxZoom: 19
                }).addTo(map);
                map.on('click', function(e) {
                    if (selectionMode === 'origin') {
                        setOrigin(e.latlng.lat, e.latlng.lng);
                        selectionMode = 'destination';
                        document.getElementById('map-action-status').textContent = 'Ahora toca en el mapa el lugar a donde quieres ir.';
                    } else {
                        setDestination(e.latlng.lat, e.latlng.lng);

                        document.getElementById('map-action-status').textContent = 'Destino marcado. Puedes tocar otro punto para cambiarlo.';
                    }
                });
            } else {
                map.setView([lat, lng], zoom);
                map.invalidateSize();
            }
            return true;
        }


        async function getMunicipalityCenter(municipality, department) {
            const query = encodeURIComponent(`${municipality}, ${department}, Colombia`);

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${query}`);
                if (!response.ok) {
                    throw new Error('No se pudo geocodificar el municipio.');
                }

                const data = await response.json();
                if (!Array.isArray(data) || data.length === 0) {
                    return null;
                }

                return {
                    lat: parseFloat(data[0].lat),
                    lng: parseFloat(data[0].lon),
                };
            } catch (error) {
                return null;
            }
        }

        async function initOrMoveMap(department, municipality) {
            if (!department || !municipality) {
                return;
            }

            const center = await getMunicipalityCenter(municipality, department);
            if (!center) {
                mapPlaceholder.textContent = 'No fue posible ubicar el municipio en el mapa.';
                return;
            }

            enableAddressInputs(true);
            ensureMap(center.lat, center.lng, 13);
        }

        function setOrigin(lat, lng) {
            document.getElementById('origin_lat').value = lat.toFixed(6);
            document.getElementById('origin_lng').value = lng.toFixed(6);
            document.getElementById('origin-display').textContent = lat.toFixed(4) + ', ' + lng.toFixed(4);

            if (!originInput.value.trim() || originInput.value.startsWith('Ubicacion actual:')) {
                originInput.value = 'Ubicacion actual: ' + lat.toFixed(5) + ', ' + lng.toFixed(5);
            }

            if (!map || typeof L === 'undefined') return;
            if (originMarker) map.removeLayer(originMarker);
            originMarker = L.marker([lat, lng]).addTo(map).bindPopup('Tu ubicacion').openPopup();
        }

        function shareCurrentLocation() {
            locationStatus.className = 'location-status';
            if (!window.isSecureContext) {
                locationStatus.textContent = 'La ubicacion requiere HTTPS. En este equipo usa http://127.0.0.1:8000.';
                locationStatus.classList.add('error');
                return;
            }
            if (!navigator.geolocation) {
                locationStatus.textContent = 'Este navegador no ofrece geolocalizacion.';
                locationStatus.classList.add('error');
                return;
            }

            shareLocationBtn.disabled = true;
            shareLocationBtn.textContent = 'Buscando ubicacion...';
            locationStatus.textContent = 'Esperando la ubicacion del dispositivo. Acepta el permiso del navegador.';

            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                ensureMap(lat, lng, 16);
                setOrigin(lat, lng);
                enableAddressInputs(true);
                selectionMode = 'destination';
                document.getElementById('map-action-status').textContent = 'Tu ubicacion esta marcada. Ahora toca en el mapa el lugar a donde quieres ir.';
                locationStatus.textContent = 'Ubicacion compartida correctamente. Precision aproximada: ' + Math.round(position.coords.accuracy) + ' metros. Selecciona el departamento y municipio si aun no lo hiciste.';
                locationStatus.classList.add('success');
                shareLocationBtn.disabled = false;
                shareLocationBtn.textContent = 'Actualizar mi ubicacion';
            }, function(error) {
                const messages = {
                    1: 'Permiso de ubicacion bloqueado. Pulsa el candado junto a la direccion, permite Ubicacion y vuelve a intentar.',
                    2: 'El dispositivo no pudo determinar la ubicacion. Activa GPS o Wi-Fi y vuelve a intentar.',
                    3: 'La ubicacion tardo demasiado. Acercate a una ventana o activa el GPS y vuelve a intentar.'
                };
                locationStatus.textContent = messages[error.code] || 'No fue posible obtener tu ubicacion.';
                locationStatus.classList.add('error');
                shareLocationBtn.disabled = false;
                shareLocationBtn.textContent = 'Reintentar ubicacion';
            }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 30000 });
        }

        function setDestination(lat, lng) {
            if (!map) {
                return;
            }

            if (destinationMarker) {
                map.removeLayer(destinationMarker);
            }

            destinationMarker = L.marker([lat, lng]).addTo(map).bindPopup('Destino').openPopup();

            document.getElementById('destination_lat').value = lat.toFixed(6);
            document.getElementById('destination_lng').value = lng.toFixed(6);
            document.getElementById('destination-display').textContent = lat.toFixed(4) + ', ' + lng.toFixed(4);

            if (!destinationInput.value.trim()) {
                destinationInput.value = 'Referencia ' + lat.toFixed(4) + ', ' + lng.toFixed(4);
            }

            if (originMarker && destinationMarker) {
                const group = new L.featureGroup([originMarker, destinationMarker]);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }

        function clearMarkers() {
            if (!map) {
                return;
            }

            if (originMarker) {
                map.removeLayer(originMarker);
                originMarker = null;
            }

            if (destinationMarker) {
                map.removeLayer(destinationMarker);
                destinationMarker = null;
            }

            document.getElementById('origin-display').textContent = 'No seleccionado';
            document.getElementById('destination-display').textContent = 'No seleccionado';
        }

        departmentSelect.addEventListener('change', async function() {
            const departmentId = getDepartmentIdByName(this.value);
            await fillMunicipalities(departmentId);
            municipalitySelect.value = '';
            clearMarkers();
            mapElement.style.display = 'none';
            mapInfo.style.display = 'none';
            mapPlaceholder.style.display = 'block';
            enableAddressInputs(false);
            selectionMode = 'origin';
        });

        municipalitySelect.addEventListener('change', async function() {
            clearMarkers();
            selectionMode = 'origin';

            if (!this.value) {
                mapElement.style.display = 'none';
                mapInfo.style.display = 'none';
                mapPlaceholder.style.display = 'block';
                enableAddressInputs(false);
                return;
            }

            enableAddressInputs(true);
            locationStatus.textContent = 'Puedes escribir la direccion o usar la ubicacion del dispositivo.';
            locationStatus.className = 'location-status';
            await initOrMoveMap(departmentSelect.value, this.value);
        });

        shareLocationBtn.addEventListener('click', function() {
            shareCurrentLocation();
        });

        document.addEventListener('DOMContentLoaded', async function() {
            await loadDepartments(oldDepartment, oldMunicipality);

            if (oldDepartment && oldMunicipality) {
                await initOrMoveMap(oldDepartment, oldMunicipality);
            } else {
                enableAddressInputs(false);
                shareLocationBtn.disabled = false;
            }

            if (oldOriginLat && oldOriginLng) {
                setOrigin(parseFloat(oldOriginLat), parseFloat(oldOriginLng));
            }

            if (oldDestinationLat && oldDestinationLng) {
                setDestination(parseFloat(oldDestinationLat), parseFloat(oldDestinationLng));
            }
        });
    </script>
    @include('partials.pwa-script')
</body>
</html>

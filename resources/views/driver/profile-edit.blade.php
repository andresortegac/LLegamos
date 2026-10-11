<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Conductor | Llegamos</title>
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

        .container {
            max-width: 850px;
            margin: 0 auto;
        }

        .card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 15px;
            padding: 28px;
        }

        .title {
            color: #38bdf8;
            margin-bottom: 8px;
            font-size: 26px;
        }

        .subtitle {
            color: #cbd5e1;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .status {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            font-size: 14px;
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.15);
            border-color: rgba(245, 158, 11, 0.4);
            color: #fcd34d;
        }

        .status-approved {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.4);
            color: #a7f3d0;
        }

        .status-rejected {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.4);
            color: #fecaca;
        }

        .notice {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #a7f3d0;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fecaca;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .group {
            margin-bottom: 16px;
        }

        .group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #e2e8f0;
        }

        .group input,
        .group select {
            width: 100%;
            padding: 11px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.25);
            background: rgba(255,255,255,0.05);
            color: white;
        }

        .group select option {
            color: #0f172a;
        }

        .hint {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .required {
            color: #ef4444;
        }

        .section-title {
            margin: 18px 0 12px;
            color: #7dd3fc;
            font-size: 16px;
        }

        .doc-links a {
            display: inline-block;
            color: #93c5fd;
            text-decoration: none;
            margin-right: 10px;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            background: #38bdf8;
            color: #0f172a;
            margin-top: 8px;
        }

        .camera-group { grid-column: 1 / -1; }
        .camera-frame { width: min(100%, 520px); aspect-ratio: 4/3; background:#020617; border:2px dashed #475569; border-radius:12px; overflow:hidden; display:flex; align-items:center; justify-content:center; }
        .camera-frame video, .camera-frame img { width:100%; height:100%; object-fit:cover; transform:scaleX(-1); }
        .camera-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
        .camera-btn { padding:10px 14px; border:0; border-radius:8px; background:#0ea5e9; color:#fff; font-weight:700; cursor:pointer; }
        .camera-btn:disabled { opacity:.45; cursor:not-allowed; }
        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @include('partials.pwa-head')
</head>
<body>
    <div class="navbar">
        <h3>Llegamos - Registro de Conductor</h3>
        <a href="{{ route('dashboard.conductor') }}" class="back-link">Volver al panel</a>
    </div>

    <div class="container">
        <div class="card">
            <h2 class="title">Completar registro de conductor</h2>
            <p class="subtitle">Debes cumplir requisitos y subir documentos para que el administrador active tu cuenta.</p>

            @if(session('success'))
                <div class="notice">{{ session('success') }}</div>
            @endif

            @if($profile)
                @if($profile->verification_status === 'approved')
                    <div class="status status-approved">Cuenta aprobada. Ya puedes operar como conductor.</div>
                @elseif($profile->verification_status === 'rejected')
                    <div class="status status-rejected">
                        Registro rechazado. Corrige y vuelve a enviar.
                        @if($profile->verification_notes)
                            <br><strong>Motivo:</strong> {{ $profile->verification_notes }}
                        @endif
                    </div>
                @else
                    <div class="status status-pending">Tu informacion esta en revision del administrador.</div>
                @endif
            @endif

            @if($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('driver-profile.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="section-title">Requisitos generales</div>
                <div class="grid">
                    <div class="group">
                        <label for="birth_date">Fecha de nacimiento <span class="required">*</span></label>
                        <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date', optional($profile?->birth_date)->format('Y-m-d')) }}" required>
                        <p class="hint">Debes ser mayor de 18 anos.</p>
                    </div>

                    <div class="group">
                        <label for="document_number">Numero de documento <span class="required">*</span></label>
                        <input type="text" name="document_number" id="document_number" value="{{ old('document_number', $profile?->document_number ?? '') }}" required>
                    </div>

                    <div class="group">
                        <label for="license_number">Numero de licencia <span class="required">*</span></label>
                        <input type="text" name="license_number" id="license_number" value="{{ old('license_number', $profile?->license_number ?? '') }}" required>
                    </div>

                    <div class="group camera-group">
                        <label>Selfie en tiempo real <span class="required">*</span></label>
                        <div class="camera-frame">
                            <video id="selfie-video" autoplay playsinline muted></video>
                            <canvas id="selfie-canvas" hidden></canvas>
                            <img id="selfie-preview" alt="Vista previa de la selfie" hidden>
                        </div>
                        <input type="hidden" name="profile_photo_capture" id="profile_photo_capture"><input type="hidden" name="liveness_token" id="liveness_token">
                        <div class="camera-actions">
                            <button type="button" class="camera-btn" id="start-camera">Activar camara</button>
                            <button type="button" class="camera-btn" id="take-selfie" disabled>Tomar foto</button>
                            <button type="button" class="camera-btn" id="retry-selfie" hidden>Repetir</button>
                        </div>
                        <p class="hint" id="camera-status">Permite el acceso a la camara. Mira de frente, sin gafas oscuras ni gorra y con buena iluminacion.</p>
                    </div>
                </div>

                <div class="section-title">Requisitos del vehiculo</div>
                <div class="grid">
                    <div class="group">
                        <label for="vehicle_type">Tipo de vehiculo <span class="required">*</span></label>
                        <select name="vehicle_type" id="vehicle_type" required>
                            <option value="">Selecciona</option>
                            <option value="auto" {{ old('vehicle_type', $profile?->vehicle_type ?? '') === 'auto' ? 'selected' : '' }}>Auto</option>
                            <option value="moto" {{ old('vehicle_type', $profile?->vehicle_type ?? '') === 'moto' ? 'selected' : '' }}>Moto</option>
                        </select>
                    </div>

                    <div class="group">
                        <label for="vehicle_plate">Placa del vehiculo <span class="required">*</span></label>
                        <input type="text" name="vehicle_plate" id="vehicle_plate" value="{{ old('vehicle_plate', $profile?->vehicle_plate ?? '') }}" required>
                    </div>

                    <div class="group">
                        <label for="vehicle_model_year">Modelo (ano) <span class="required">*</span></label>
                        <input type="number" name="vehicle_model_year" id="vehicle_model_year" min="{{ $currentYear - 10 }}" max="{{ $currentYear }}" value="{{ old('vehicle_model_year', $profile?->vehicle_model_year ?? '') }}" required>
                        <p class="hint">Maximo 10 anos de antiguedad.</p>
                    </div>

                    <div class="group">
                        <label for="plate_type">Tipo de placa <span class="required">*</span></label>
                        <select name="plate_type" id="plate_type" required>
                            <option value="">Selecciona</option>
                            <option value="particular" {{ old('plate_type', $profile?->plate_type ?? '') === 'particular' ? 'selected' : '' }}>Particular</option>
                            <option value="publico" {{ old('plate_type', $profile?->plate_type ?? '') === 'publico' ? 'selected' : '' }}>Publico</option>
                        </select>
                    </div>

                    <div class="group">
                        <label for="has_four_doors">Auto con 4 puertas</label>
                        <select name="has_four_doors" id="has_four_doors">
                            <option value="0" {{ (string) old('has_four_doors', (int) ($profile?->has_four_doors ?? 0)) === '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ (string) old('has_four_doors', (int) ($profile?->has_four_doors ?? 0)) === '1' ? 'selected' : '' }}>Si</option>
                        </select>
                    </div>

                    <div class="group">
                        <label for="has_seatbelts">Cinturones para todos <span class="required">*</span></label>
                        <select name="has_seatbelts" id="has_seatbelts" required>
                            <option value="0" {{ (string) old('has_seatbelts', (int) ($profile?->has_seatbelts ?? 0)) === '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ (string) old('has_seatbelts', (int) ($profile?->has_seatbelts ?? 0)) === '1' ? 'selected' : '' }}>Si</option>
                        </select>
                    </div>

                    <div class="group">
                        <label for="has_air_conditioning">Aire acondicionado</label>
                        <select name="has_air_conditioning" id="has_air_conditioning">
                            <option value="0" {{ (string) old('has_air_conditioning', (int) ($profile?->has_air_conditioning ?? 0)) === '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ (string) old('has_air_conditioning', (int) ($profile?->has_air_conditioning ?? 0)) === '1' ? 'selected' : '' }}>Si</option>
                        </select>
                    </div>
                </div>

                <div class="section-title">Identificación del vehículo</div>
                <div class="grid">
                    <div class="group"><label>Marca <span class="required">*</span></label><input name="vehicle_make" value="{{ old('vehicle_make', $profile?->vehicle_make) }}" required></div>
                    <div class="group"><label>Línea / modelo comercial <span class="required">*</span></label><input name="vehicle_model" value="{{ old('vehicle_model', $profile?->vehicle_model) }}" required></div>
                    <div class="group"><label>Color <span class="required">*</span></label><input name="vehicle_color" value="{{ old('vehicle_color', $profile?->vehicle_color) }}" required></div>
                    <div class="group"><label>Capacidad de pasajeros <span class="required">*</span></label><input type="number" min="1" max="8" name="passenger_capacity" value="{{ old('passenger_capacity', $profile?->passenger_capacity) }}" required></div>
                    <div class="group"><label>¿El vehículo está a tu nombre?</label><select name="is_vehicle_owner" required><option value="1">Sí</option><option value="0" {{ old('is_vehicle_owner', $profile?->is_vehicle_owner) === '0' ? 'selected' : '' }}>No</option></select></div>
                    <div class="group"><label>Autorización del propietario (si no es tuyo)</label><input type="file" name="owner_authorization_document" accept=".pdf,image/*"></div>
                </div>
                <div class="section-title">Documentacion obligatoria</div>
                <div class="grid">
                    <div class="group">
                        <label for="license_document">Licencia vigente <span class="required">*</span></label><input type="date" name="license_expires_at" value="{{ old('license_expires_at', optional($profile?->license_expires_at)->format('Y-m-d')) }}" required>
                        <input type="file" name="license_document" id="license_document" accept=".pdf,image/*">
                    </div>

                    <div class="group">
                        <label for="id_card_document">Cedula / documento de identidad <span class="required">*</span></label>
                        <input type="file" name="id_card_document" id="id_card_document" accept=".pdf,image/*">
                    </div>

                    <div class="group">
                        <label for="property_card_document">Tarjeta de propiedad <span class="required">*</span></label>
                        <input type="file" name="property_card_document" id="property_card_document" accept=".pdf,image/*">
                    </div>

                    <div class="group">
                        <label for="soat_document">SOAT vigente <span class="required">*</span></label><input type="date" name="soat_expires_at" value="{{ old('soat_expires_at', optional($profile?->soat_expires_at)->format('Y-m-d')) }}" required>
                        <input type="file" name="soat_document" id="soat_document" accept=".pdf,image/*">
                    </div>
                </div>

                <div class="grid">
                    <div class="group"><label>Revisión técnico-mecánica vigente <span class="required">*</span></label><input type="date" name="technical_review_expires_at" value="{{ old('technical_review_expires_at', optional($profile?->technical_review_expires_at)->format('Y-m-d')) }}" required><input type="file" name="technical_review_document" accept=".pdf,image/*"></div>
                </div>
                <div class="section-title">Consentimientos y seguridad</div>
                <div class="group"><label><input type="checkbox" name="background_check_consent" value="1" required> Autorizo la consulta de antecedentes judiciales y de conducción.</label></div>
                <div class="group"><label><input type="checkbox" name="training_completed" value="1" required> Confirmo que completé la capacitación de seguridad vial y atención al usuario.</label></div>
                <div class="group"><label><input type="checkbox" name="policies_accepted" value="1" required> Acepto las políticas de seguridad, cero tolerancia al alcohol y drogas, no discriminación, privacidad y normas de la comunidad.</label></div>
                @if($profile)
                    <div class="doc-links">
                        @if($profile->profile_photo_path)
                            <a href="{{ route('driver-profile.document', [$profile->id, 'profile']) }}" target="_blank">Ver foto de perfil</a>
                        @endif
                        @if($profile->license_document_path)
                            <a href="{{ route('driver-profile.document', [$profile->id, 'license']) }}" target="_blank">Ver licencia</a>
                        @endif
                        @if($profile->property_card_path)
                            <a href="{{ route('driver-profile.document', [$profile->id, 'property-card']) }}" target="_blank">Ver tarjeta propiedad</a>
                        @endif
                        @if($profile->id_card_document_path)
                            <a href="{{ route('driver-profile.document', [$profile->id, 'id-card']) }}" target="_blank">Ver cédula / documento identidad</a>
                        @endif
                        @if($profile->soat_document_path)
                            <a href="{{ route('driver-profile.document', [$profile->id, 'soat']) }}" target="_blank">Ver SOAT</a>
                        @endif
                    </div>
                @endif

                <button type="submit" class="btn">Enviar para verificacion</button>
            </form>
        </div>
    </div>
<script>
(() => {
    const video = document.getElementById('selfie-video');
    const canvas = document.getElementById('selfie-canvas');
    const preview = document.getElementById('selfie-preview');
    const capture = document.getElementById('profile_photo_capture');
    const livenessToken = document.getElementById('liveness_token');
    const start = document.getElementById('start-camera');
    const take = document.getElementById('take-selfie');
    const retry = document.getElementById('retry-selfie');
    const status = document.getElementById('camera-status');
    let stream = null;

    async function startCamera() {
        try {
            if (!navigator.mediaDevices?.getUserMedia) throw new Error('unsupported');
            const challengeResponse = await fetch('{{ route('driver-profile.liveness') }}',{headers:{Accept:'application/json'}});
            if(!challengeResponse.ok) throw new Error('challenge');
            const challenge = await challengeResponse.json(); livenessToken.value=challenge.token;
            stream = await navigator.mediaDevices.getUserMedia({video:{facingMode:'user',width:{ideal:1280},height:{ideal:960}},audio:false});
            video.srcObject = stream;
            video.hidden = false; preview.hidden = true; take.disabled = false; retry.hidden = true;
            status.textContent = 'Desafio: '+challenge.instruction+' Luego toma la foto antes de 3 minutos.';
            take.disabled=true; setTimeout(()=>{if(stream)take.disabled=false;},2500);
        } catch (error) {
            status.textContent = 'No fue posible abrir la camara. Revisa el permiso del navegador y usa HTTPS o localhost.';
        }
    }

    start.addEventListener('click', startCamera);
    take.addEventListener('click', () => {
        if (!stream || video.videoWidth < 320 || video.videoHeight < 320) return;
        canvas.width = video.videoWidth; canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');
        context.translate(canvas.width, 0); context.scale(-1, 1); context.drawImage(video, 0, 0);
        capture.value = canvas.toDataURL('image/jpeg', .88);
        preview.src = capture.value; preview.hidden = false; video.hidden = true;
        stream.getTracks().forEach(track => track.stop()); stream = null;
        take.disabled = true; retry.hidden = false; status.textContent = 'Selfie capturada. Puedes repetirla si no esta clara.';
    });
    retry.addEventListener('click', () => { capture.value = ''; livenessToken.value=''; startCamera(); });
    document.querySelector('form[action="{{ route('driver-profile.update') }}"]').addEventListener('submit', event => {
        if (!capture.value && !@json((bool) $profile?->profile_photo_path)) {
            event.preventDefault(); status.textContent = 'Debes tomar la selfie antes de enviar el registro.'; start.scrollIntoView({behavior:'smooth',block:'center'});
        }
    });
    window.addEventListener('beforeunload', () => stream?.getTracks().forEach(track => track.stop()));
})();
</script>
    @include('partials.pwa-script')
</body>
</html>

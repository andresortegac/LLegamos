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

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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

                    <div class="group">
                        <label for="profile_photo">Foto de perfil clara <span class="required">*</span></label>
                        <input type="file" name="profile_photo" id="profile_photo" accept="image/*">
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

                <div class="section-title">Documentacion obligatoria</div>
                <div class="grid">
                    <div class="group">
                        <label for="license_document">Licencia vigente <span class="required">*</span></label>
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
                        <label for="soat_document">SOAT vigente <span class="required">*</span></label>
                        <input type="file" name="soat_document" id="soat_document" accept=".pdf,image/*">
                    </div>
                </div>

                @if($profile)
                    <div class="doc-links">
                        @if($profile->profile_photo_path)
                            <a href="{{ asset('storage/' . $profile->profile_photo_path) }}" target="_blank">Ver foto de perfil</a>
                        @endif
                        @if($profile->license_document_path)
                            <a href="{{ asset('storage/' . $profile->license_document_path) }}" target="_blank">Ver licencia</a>
                        @endif
                        @if($profile->property_card_path)
                            <a href="{{ asset('storage/' . $profile->property_card_path) }}" target="_blank">Ver tarjeta propiedad</a>
                        @endif
                        @if($profile->id_card_document_path)
                            <a href="{{ asset('storage/' . $profile->id_card_document_path) }}" target="_blank">Ver cédula / documento identidad</a>
                        @endif
                        @if($profile->soat_document_path)
                            <a href="{{ asset('storage/' . $profile->soat_document_path) }}" target="_blank">Ver SOAT</a>
                        @endif
                    </div>
                @endif

                <button type="submit" class="btn">Enviar para verificacion</button>
            </form>
        </div>
    </div>
</body>
</html>

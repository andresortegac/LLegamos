<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Conductor | Llegamos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #0f172a, #1e293b); color: white; padding: 20px; min-height: 100vh; }
        .container { max-width: 1000px; margin: 0 auto; }
        .navbar { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); padding: 14px 22px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .navbar h3 { color: #38bdf8; }
        .back-link, .btn { text-decoration: none; color: #0f172a; background: #38bdf8; border-radius: 8px; padding: 9px 14px; font-weight: 600; }
        .card { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; padding: 18px; }
        .section-title { color: #38bdf8; font-size: 20px; margin-bottom: 12px; }
        .property { margin-bottom: 8px; font-size: 14px; }
        .doc-links a { display: block; color: #93c5fd; margin-bottom: 6px; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
        .btn-approve { background: #10b981; color: white; border: none; padding: 10px 14px; border-radius: 8px; cursor: pointer; }
        .btn-reject { background: #ef4444; color: white; border: none; padding: 10px 14px; border-radius: 8px; cursor: pointer; }
        textarea { width: 100%; min-height: 80px; border-radius: 8px; border: 1px solid #374151; background: rgba(255,255,255,0.05); color: #fff; padding: 8px; margin-top: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h3>Detalle de solicitud de conductor</h3>
            <a href="{{ route('dashboard.admin') }}" class="back-link">Volver al panel</a>
        </div>

        @if(session('success'))
            <div style="margin-bottom: 10px; background: rgba(16, 185, 129, 0.2); border:1px solid rgba(16, 185, 129, 0.4); padding: 10px; border-radius: 8px; color: #a7f3d0;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div style="margin-bottom: 10px; background: rgba(239, 68, 68, 0.2); border:1px solid rgba(239, 68, 68, 0.4); padding: 10px; border-radius: 8px; color: #fecaca;">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="section-title">Datos de Conductor</div>
            <div class="property"><strong>Nombre:</strong> {{ $profile->user->name }}</div>
            <div class="property"><strong>Correo:</strong> {{ $profile->user->email }}</div>
            <div class="property"><strong>Documento:</strong> {{ $profile->document_number }}</div>
            <div class="property"><strong>Licencia:</strong> {{ $profile->license_number }}</div>
            <div class="property"><strong>Vehiculo:</strong> {{ strtoupper($profile->vehicle_type) }} {{ $profile->vehicle_model_year ? '(' . $profile->vehicle_model_year . ')' : '' }}</div>
            <div class="property"><strong>Placa:</strong> {{ $profile->vehicle_plate }} ({{ $profile->plate_type }})</div>
            <div class="property"><strong>Cinturones:</strong> {{ $profile->has_seatbelts ? 'Sí' : 'No' }}</div>
            <div class="property"><strong>4 puertas:</strong> {{ $profile->has_four_doors ? 'Sí' : 'No' }}</div>
            <div class="property"><strong>Aire acondicionado:</strong> {{ $profile->has_air_conditioning ? 'Sí' : 'No' }}</div>
            <div class="property"><strong>Estado verificación:</strong> {{ ucfirst($profile->verification_status) }}</div>
        </div>

        <div class="card" style="margin-top: 16px;">
            <div class="section-title">Documentos subidos</div>
            <div class="doc-links">
                @if($profile->profile_photo_path)
                    <a href="{{ asset('storage/' . $profile->profile_photo_path) }}" target="_blank">Foto de perfil</a>
                @endif
                @if($profile->license_document_path)
                    <a href="{{ asset('storage/' . $profile->license_document_path) }}" target="_blank">Licencia (documento)</a>
                @endif
                @if($profile->id_card_document_path)
                    <a href="{{ asset('storage/' . $profile->id_card_document_path) }}" target="_blank">Cédula / ID</a>
                @endif
                @if($profile->property_card_path)
                    <a href="{{ asset('storage/' . $profile->property_card_path) }}" target="_blank">Tarjeta de propiedad</a>
                @endif
                @if($profile->soat_document_path)
                    <a href="{{ asset('storage/' . $profile->soat_document_path) }}" target="_blank">SOAT</a>
                @endif
            </div>
        </div>

        <div class="card" style="margin-top: 16px;">
            <div class="section-title">Acciones de aprobación</div>
            <div class="actions">
                <form method="POST" action="{{ route('driver-profile.approve', $profile->id) }}">
                    @csrf
                    <button type="submit" class="btn-approve">Aprobar conductor</button>
                </form>
                <form method="POST" action="{{ route('driver-profile.reject', $profile->id) }}">
                    @csrf
                    <textarea name="verification_notes" placeholder="Motivo del rechazo" required></textarea>
                    <button type="submit" class="btn-reject">Rechazar</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
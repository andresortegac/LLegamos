<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador | Llegamos</title>
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
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

        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .welcome {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 24px;
        }

        .welcome h2 {
            color: #38bdf8;
            margin-bottom: 8px;
        }

        .welcome p {
            color: #cbd5e1;
        }

        .section-title {
            color: #38bdf8;
            margin: 26px 0 16px;
            font-size: 24px;
        }

        .notice {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #a7f3d0;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fecaca;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .table-box {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 15px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            vertical-align: top;
            text-align: left;
        }

        th {
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
        }

        td {
            color: #cbd5e1;
            font-size: 13px;
        }

        .doc-links a {
            color: #93c5fd;
            text-decoration: none;
            display: block;
            margin-bottom: 4px;
        }

        .actions {
            min-width: 240px;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-approve {
            background: #10b981;
            color: #fff;
            margin-bottom: 8px;
            width: 100%;
        }

        .btn-reject {
            background: #ef4444;
            color: #fff;
            width: 100%;
            margin-top: 8px;
        }

        textarea {
            width: 100%;
            min-height: 72px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.25);
            background: rgba(255,255,255,0.05);
            color: #fff;
            padding: 8px;
        }

        .empty {
            text-align: center;
            color: #cbd5e1;
            padding: 28px;
        }

        .pill {
            display: inline-block;
            margin-left: 8px;
            background: #ef4444;
            color: #fff;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <h3>Llegamos - Panel de Administrador</h3>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">Cerrar sesion</button>
            </form>
        </div>

        <div class="welcome">
            <h2>Bienvenido, {{ $user['name'] }}</h2>
            <p>Gestiona la aprobacion de conductores antes de habilitarles la cuenta.</p>
            <a href="{{ route('messages.index') }}" style="text-decoration: none; display: inline-block; margin-top: 10px;">
                <button type="button" class="btn btn-approve" style="width: auto;">
                    Mensajes con conductores
                    @if(($unreadMessagesCount ?? 0) > 0)
                        <span class="pill">{{ $unreadMessagesCount }}</span>
                    @endif
                </button>
            </a>
        </div>

        @if(session('success'))
            <div class="notice">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <h3 class="section-title">Solicitudes de conductores pendientes</h3>

        <div class="table-box">
            @if($pendingDrivers->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Conductor</th>
                            <th>Datos clave</th>
                            <th>Documentos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingDrivers as $profile)
                            <tr>
                                <td>
                                    <strong>{{ $profile->user->name }}</strong><br>
                                    {{ $profile->user->email }}<br>
                                    Enviado: {{ optional($profile->submitted_at)->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td>
                                    Documento: {{ $profile->document_number }}<br>
                                    Licencia: {{ $profile->license_number }}<br>
                                    Placa: {{ $profile->vehicle_plate }}<br>
                                    Vehiculo: {{ strtoupper($profile->vehicle_type) }} {{ $profile->vehicle_model_year ? '(' . $profile->vehicle_model_year . ')' : '' }}<br>
                                    Placa tipo: {{ $profile->plate_type }}<br>
                                    Cinturones: {{ $profile->has_seatbelts ? 'Si' : 'No' }}<br>
                                    4 puertas: {{ $profile->has_four_doors ? 'Si' : 'No' }}<br>
                                    Aire acondicionado: {{ $profile->has_air_conditioning ? 'Si' : 'No' }}
                                </td>
                                <td class="doc-links">
                                    @if($profile->profile_photo_path)
                                        <a href="{{ asset('storage/' . $profile->profile_photo_path) }}" target="_blank">Foto de perfil</a>
                                    @endif
                                    @if($profile->license_document_path)
                                        <a href="{{ asset('storage/' . $profile->license_document_path) }}" target="_blank">Licencia</a>
                                    @endif
                                    @if($profile->property_card_path)
                                        <a href="{{ asset('storage/' . $profile->property_card_path) }}" target="_blank">Tarjeta propiedad</a>
                                    @endif
                                    @if($profile->id_card_document_path)
                                        <a href="{{ asset('storage/' . $profile->id_card_document_path) }}" target="_blank">Cédula / documento identidad</a>
                                    @endif
                                    @if($profile->soat_document_path)
                                        <a href="{{ asset('storage/' . $profile->soat_document_path) }}" target="_blank">SOAT</a>
                                    @endif
                                </td>
                                <td class="actions">
                                    <a href="{{ route('driver-profile.detail', $profile->id) }}" class="btn" style="background: #f59e0b; margin-bottom: 8px;">Ver detalles</a>

                                    <form method="POST" action="{{ route('driver-profile.approve', $profile->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-approve">Aprobar conductor</button>
                                    </form>

                                    <form method="POST" action="{{ route('driver-profile.reject', $profile->id) }}">
                                        @csrf
                                        <textarea name="verification_notes" placeholder="Motivo del rechazo" required></textarea>
                                        <button type="submit" class="btn btn-reject">Rechazar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty">No hay solicitudes pendientes de verificacion.</div>
            @endif
        </div>
    </div>
</body>
</html>

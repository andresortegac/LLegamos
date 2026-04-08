<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Conductor | Llegamos</title>
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

        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-section {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            color: #38bdf8;
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: #cbd5e1;
        }

        .status-badge {
            display: inline-block;
            color: #0f172a;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
            background: #10b981;
        }

        .status-pending {
            background: #f59e0b;
            color: #111827;
        }

        .status-rejected {
            background: #ef4444;
            color: #ffffff;
        }

        .section-title {
            color: #38bdf8;
            margin: 30px 0 20px 0;
            font-size: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-card h4 {
            color: #cbd5e1;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-value {
            color: #38bdf8;
            font-size: 32px;
            font-weight: bold;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
        }

        .card h4 {
            color: #38bdf8;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .card p {
            color: #cbd5e1;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .btn {
            background: #38bdf8;
            color: #0f172a;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #0ea5e9;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .status-panel,
        .empty-state {
            text-align: center;
            padding: 25px;
            color: #cbd5e1;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 15px;
            margin-top: 15px;
        }

        .status-panel p,
        .empty-state p {
            font-size: 16px;
            margin-bottom: 14px;
        }

        .status-panel strong {
            color: #fca5a5;
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
            <h3>Llegamos - Panel de Conductor</h3>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">Cerrar sesion</button>
            </form>
        </div>

        <div class="welcome-section">
            <h2>Bienvenido, {{ $user['name'] }}!</h2>
            <p>Tipo de usuario: <strong>Conductor</strong></p>

            @if($canOperate)
                <div class="status-badge">Cuenta activa</div>
            @elseif($profile && $profile->verification_status === 'rejected')
                <div class="status-badge status-rejected">Registro rechazado</div>
            @else
                <div class="status-badge status-pending">Registro en revision</div>
            @endif
        </div>

        @if(!$canOperate)
            <div class="status-panel">
                <p>Tu cuenta de conductor esta bloqueada hasta completar requisitos y aprobar verificacion administrativa.</p>

                @if(!$profile)
                    <p>Debes completar tu registro de conductor con todos los documentos requeridos.</p>
                @elseif($profile->verification_status === 'pending')
                    <p>Tu informacion fue enviada y esta siendo revisada por el administrador.</p>
                @elseif($profile->verification_status === 'rejected')
                    <p>Tu solicitud fue rechazada. Corrige y vuelve a enviar.</p>
                    @if($profile->verification_notes)
                        <p><strong>Motivo:</strong> {{ $profile->verification_notes }}</p>
                    @endif
                @endif

                <a href="{{ route('driver-profile.show') }}" style="text-decoration: none;">
                    <button class="btn" type="button">Completar registro</button>
                </a>
                <a href="{{ route('messages.index') }}" style="text-decoration: none;">
                    <button class="btn btn-secondary" style="margin-top: 10px;" type="button">
                        Mensajes con administrador
                        @if(($unreadMessagesCount ?? 0) > 0)
                            <span class="pill">{{ $unreadMessagesCount }}</span>
                        @endif
                    </button>
                </a>
            </div>
        @else
            <h3 class="section-title">Estadisticas</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Viajes completados</h4>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-card">
                    <h4>Ingresos hoy</h4>
                    <div class="stat-value">$0</div>
                </div>
                <div class="stat-card">
                    <h4>Calificacion</h4>
                    <div class="stat-value">5.0</div>
                </div>
                <div class="stat-card">
                    <h4>Horas activo</h4>
                    <div class="stat-value">0h</div>
                </div>
            </div>

            <h3 class="section-title">Acciones</h3>
            <div class="grid">
                <div class="card">
                    <h4>Mi perfil</h4>
                    <p>Actualiza tu informacion y datos del vehiculo.</p>
                    <a href="{{ route('driver-profile.show') }}" style="text-decoration: none;">
                        <button class="btn" type="button">Ver perfil</button>
                    </a>
                </div>

                <div class="card">
                    <h4>Mis viajes</h4>
                    <p>Historial de viajes realizados.</p>
                    <a href="{{ route('trip.history') }}" style="text-decoration: none;">
                        <button class="btn btn-secondary" type="button">Ver viajes</button>
                    </a>
                </div>

                <div class="card">
                    <h4>Mis ganancias</h4>
                    <p>Resumen de ganancias y pagos.</p>
                    <a href="{{ route('payment.earnings') }}" style="text-decoration: none;">
                        <button class="btn btn-secondary" type="button">Ver ganancias</button>
                    </a>
                </div>

                <div class="card">
                    <h4>Mensajeria interna</h4>
                    <p>Comunicate con el administrador de la plataforma.</p>
                    <a href="{{ route('messages.index') }}" style="text-decoration: none;">
                        <button class="btn btn-secondary" type="button">
                            Abrir mensajes
                            @if(($unreadMessagesCount ?? 0) > 0)
                                <span class="pill">{{ $unreadMessagesCount }}</span>
                            @endif
                        </button>
                    </a>
                </div>
            </div>

            <h3 class="section-title">Solicitudes de viaje</h3>
            <div class="empty-state">
                <p>Encuentra viajes disponibles y acepta para empezar a ganar.</p>
                <a href="{{ route('trip.list-pending') }}" style="text-decoration: none;">
                    <button class="btn" type="button">Ver viajes disponibles</button>
                </a>
            </div>

            <h3 class="section-title">Viaje activo</h3>
            <div class="empty-state">
                <p>No tienes viajes activos en este momento.</p>
            </div>
        @endif
    </div>
</body>
</html>

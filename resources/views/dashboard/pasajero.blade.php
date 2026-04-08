<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Pasajero | Llegamos</title>
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

        .section-title {
            color: #38bdf8;
            margin: 30px 0 20px 0;
            font-size: 24px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #cbd5e1;
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navbar -->
        <div class="navbar">
            <h3>🚕 Llegamos - Panel de Pasajero</h3>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">Cerrar sesión</button>
            </form>
        </div>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2>Bienvenido, {{ $user['name'] }}!</h2>
            <p>Tipo de usuario: <strong>Pasajero</strong></p>
        </div>

        <!-- Main Actions -->
        <h3 class="section-title">Acciones rápidas</h3>
        <div class="grid">
            <div class="card">
                <h4>🚕 Solicitar viaje</h4>
                <p>Pide un viaje ahora y conecta con conductores cercanos</p>
                <a href="{{ route('trip.create-request') }}" style="text-decoration: none;">
                    <button class="btn" type="button">Solicitar viaje</button>
                </a>
            </div>

            <div class="card">
                <h4>📍 Mis viajes</h4>
                <p>Ver historial de viajes completados</p>
                <a href="{{ route('trip.history') }}" style="text-decoration: none;">
                    <button class="btn btn-secondary" type="button">Ver historial</button>
                </a>
            </div>

            <div class="card">
                <h4>⭐ Mis favoritos</h4>
                <p>Conductores y lugares favoritos</p>
                <button class="btn btn-secondary" type="button">Mis favoritos</button>
            </div>
        </div>

        <!-- Active Trip -->
        <h3 class="section-title">Viaje activo</h3>
        <div class="empty-state">
            <p>No tienes ningún viaje activo en este momento</p>
            <a href="{{ route('trip.create-request') }}" style="text-decoration: none;">
                <button class="btn" type="button">Solicitar viaje ahora</button>
            </a>
        </div>

        <!-- Recent Activity -->
        <h3 class="section-title">Actividad reciente</h3>
        <div class="empty-state">
            <p>No tienes viajes registrados aún</p>
        </div>
    </div>
</body>
</html>

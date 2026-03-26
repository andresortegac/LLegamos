<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Llegamos | Inicio</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: white;
            min-height: 100vh;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #38bdf8;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: calc(100vh - 80px);
            padding: 0 50px;
            gap: 30px;
        }

        .hero-text {
            max-width: 600px;
        }

        .hero-text h1 {
            font-size: 50px;
            margin-bottom: 20px;
        }

        .hero-text p {
            font-size: 18px;
            color: #cbd5e1;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .buttons a {
            display: inline-block;
            padding: 14px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            margin-right: 10px;
        }

        .btn-primary {
            background: #38bdf8;
            color: #0f172a;
        }

        .btn-secondary {
            border: 1px solid #38bdf8;
            color: #38bdf8;
        }

        .card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 30px;
            border-radius: 20px;
            width: 360px;
        }

        .card h3 {
            margin-bottom: 15px;
        }

        .card p {
            margin-bottom: 10px;
            color: #e2e8f0;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">Llegamos</div>
        <div class="nav-links">
            <a href="{{ route('welcome') }}">Inicio</a>
            <a href="{{ route('register') }}">Registro</a>
            <a href="{{ route('login') }}">Ingresar</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-text">
            <h1>Tu transporte rápido y seguro</h1>
            <p>
                Llegamos conecta pasajeros y conductores en una plataforma moderna,
                confiable y lista para crecer como una app de transporte profesional.
            </p>

            <div class="buttons">
                <a href="{{ route('register') }}" class="btn-primary">Crear cuenta</a>
                <a href="{{ route('login') }}" class="btn-secondary">Iniciar sesión</a>
            </div>
        </div>

        <div class="card">
            <h3>Primeros módulos de Llegamos</h3>
            <p>• Registro de pasajeros</p>
            <p>• Registro de conductores</p>
            <p>• Inicio de sesión</p>
            <p>• Panel principal</p>
            <p>• Base para viajes y geolocalización</p>
        </div>
    </section>
</body>
</html>
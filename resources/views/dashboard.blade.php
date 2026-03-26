<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Llegamos</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8fafc;
        }

        .header {
            background: #0f172a;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            padding: 40px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            max-width: 750px;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            cursor: pointer;
        }

        ul {
            margin-top: 15px;
        }

        li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Panel principal - Llegamos</h2>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="logout-btn" type="submit">Cerrar sesión</button>
        </form>
    </div>

    <div class="container">
        <div class="card">
            <h3>Hola, {{ $user['name'] }}</h3>
            <p><strong>Correo:</strong> {{ $user['email'] }}</p>
            <p><strong>Rol:</strong> {{ $user['role'] }}</p>

            <hr>

            <p>Tu cuenta fue creada correctamente y ya ingresaste al sistema.</p>
            <p>La base inicial de <strong>Llegamos</strong> ya está funcionando.</p>

            <ul>
                <li>Registro de usuarios</li>
                <li>Inicio de sesión</li>
                <li>Sesión activa</li>
                <li>Dashboard principal</li>
            </ul>
        </div>
    </div>
</body>
</html>
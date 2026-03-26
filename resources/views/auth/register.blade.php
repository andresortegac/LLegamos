<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | Llegamos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: white;
            padding: 30px 0;
        }

        .box {
            width: 100%;
            max-width: 460px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 35px;
            border-radius: 20px;
        }

        h2 {
            text-align: center;
            color: #38bdf8;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
            margin-bottom: 25px;
            color: #cbd5e1;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 13px;
            border-radius: 10px;
            border: none;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            background: #38bdf8;
            color: #0f172a;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
        }

        .error {
            background: rgba(239, 68, 68, 0.2);
            color: #fecaca;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .links {
            text-align: center;
            margin-top: 18px;
        }

        .links a {
            color: #93c5fd;
            text-decoration: none;
            margin: 0 8px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Crear cuenta en Llegamos</h2>
        <p>Registra tu usuario para entrar a la plataforma</p>

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}">
            @csrf

            <div class="form-group">
                <label for="name">Nombre completo</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="role">Tipo de usuario</label>
                <select name="role" id="role" required>
                    <option value="">Selecciona una opción</option>
                    <option value="pasajero" {{ old('role') == 'pasajero' ? 'selected' : '' }}>Pasajero</option>
                    <option value="conductor" {{ old('role') == 'conductor' ? 'selected' : '' }}>Conductor</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required>
            </div>

            <button type="submit" class="btn">Registrarme</button>
        </form>

        <div class="links">
            <a href="{{ route('welcome') }}">Inicio</a>
            <a href="{{ route('login') }}">Ya tengo cuenta</a>
        </div>
    </div>
</body>
</html>
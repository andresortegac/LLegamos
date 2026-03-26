<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Llegamos</title>
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
        }

        .box {
            width: 100%;
            max-width: 420px;
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
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
        }

        .form-group input {
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
        <h2>Ingresar a Llegamos</h2>
        <p>Escribe tus datos para continuar</p>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="btn">Ingresar</button>
        </form>

        <div class="links">
            <a href="{{ route('welcome') }}">Inicio</a>
            <a href="{{ route('register') }}">Crear cuenta</a>
        </div>
    </div>
</body>
</html>
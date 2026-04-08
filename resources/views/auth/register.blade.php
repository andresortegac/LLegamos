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
            max-width: 520px;
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

        .required {
            color: #f87171;
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

        .btn:hover {
            background: #0ea5e9;
        }

        .btn-secondary {
            background: #64748b;
            color: #fff;
            margin-top: 8px;
        }

        .btn-secondary:hover {
            background: #475569;
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

        .security-box {
            background: rgba(15, 23, 42, 0.45);
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 12px;
            padding: 16px;
            margin: 16px 0;
            display: none;
        }

        .security-box h4 {
            color: #7dd3fc;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .security-hint {
            font-size: 12px;
            color: #cbd5e1;
            margin-bottom: 10px;
            text-align: left;
        }

        .camera-wrap {
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 10px;
            overflow: hidden;
            background: #0b1220;
        }

        #biometric-video {
            width: 100%;
            max-height: 240px;
            display: none;
            background: #000;
        }

        #biometric-preview {
            width: 100%;
            max-height: 240px;
            display: none;
        }

        .camera-msg {
            text-align: center;
            padding: 18px;
            color: #94a3b8;
            font-size: 13px;
        }

        .camera-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .camera-actions button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .capture-btn {
            background: #22c55e;
            color: #052e16;
        }

        .start-btn {
            background: #38bdf8;
            color: #0f172a;
        }

        .retake-btn {
            background: #f59e0b;
            color: #422006;
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

        <form method="POST" action="{{ route('register.submit') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="name">Nombre completo</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label for="email">Correo electronico</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="role">Tipo de usuario</label>
                <select name="role" id="role" required>
                    <option value="">Selecciona una opcion</option>
                    <option value="pasajero" {{ old('role') == 'pasajero' ? 'selected' : '' }}>Pasajero</option>
                    <option value="conductor" {{ old('role') == 'conductor' ? 'selected' : '' }}>Conductor</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
            </div>

            <div class="security-box" id="passenger-security-box">
                <h4>Validacion de identidad para pasajeros</h4>
                <p class="security-hint">Para seguridad del conductor, el pasajero debe subir cédula por ambos lados y captura facial biométrica.</p>

                <div class="form-group">
                    <label for="id_document_front">Cedula (frente) <span class="required">*</span></label>
                    <input type="file" name="id_document_front" id="id_document_front" accept="image/*" disabled>
                </div>

                <div class="form-group">
                    <label for="id_document_back">Cedula (reverso) <span class="required">*</span></label>
                    <input type="file" name="id_document_back" id="id_document_back" accept="image/*" disabled>
                </div>

                <div class="form-group">
                    <label>Biometria facial <span class="required">*</span></label>
                    <div class="camera-wrap">
                        <video id="biometric-video" autoplay muted playsinline></video>
                        <img id="biometric-preview" alt="Vista previa biometria">
                        <div class="camera-msg" id="camera-msg">Activa la camara y toma una foto frontal del rostro.</div>
                    </div>

                    <div class="camera-actions">
                        <button type="button" class="start-btn" id="start-camera">Activar camara</button>
                        <button type="button" class="capture-btn" id="capture-face" disabled>Tomar foto</button>
                        <button type="button" class="retake-btn" id="retake-face" disabled>Repetir</button>
                    </div>

                    <input type="hidden" name="face_biometric_capture" id="face_biometric_capture" value="{{ old('face_biometric_capture') }}" disabled>
                    <canvas id="biometric-canvas" style="display: none;"></canvas>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contrasena</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar contrasena</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required>
            </div>

            <button type="submit" class="btn">Registrarme</button>
        </form>

        <div class="links">
            <a href="{{ route('welcome') }}">Inicio</a>
            <a href="{{ route('login') }}">Ya tengo cuenta</a>
        </div>
    </div>

    <script>
        const roleSelect = document.getElementById('role');
        const securityBox = document.getElementById('passenger-security-box');
        const frontInput = document.getElementById('id_document_front');
        const backInput = document.getElementById('id_document_back');

        const startBtn = document.getElementById('start-camera');
        const captureBtn = document.getElementById('capture-face');
        const retakeBtn = document.getElementById('retake-face');
        const cameraMsg = document.getElementById('camera-msg');
        const video = document.getElementById('biometric-video');
        const preview = document.getElementById('biometric-preview');
        const canvas = document.getElementById('biometric-canvas');
        const faceCaptureInput = document.getElementById('face_biometric_capture');

        let stream = null;

        function updateSecurityFields() {
            const isPassenger = roleSelect.value === 'pasajero';
            securityBox.style.display = isPassenger ? 'block' : 'none';
            frontInput.required = isPassenger;
            backInput.required = isPassenger;
            faceCaptureInput.required = isPassenger;
            frontInput.disabled = !isPassenger;
            backInput.disabled = !isPassenger;
            faceCaptureInput.disabled = !isPassenger;

            if (!isPassenger) {
                faceCaptureInput.value = '';
                frontInput.value = '';
                backInput.value = '';
                preview.style.display = 'none';
                video.style.display = 'none';
                cameraMsg.style.display = 'block';
                stopCamera();
            }
        }

        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                video.srcObject = stream;
                video.style.display = 'block';
                preview.style.display = 'none';
                cameraMsg.style.display = 'none';
                captureBtn.disabled = false;
                retakeBtn.disabled = true;
            } catch (error) {
                cameraMsg.style.display = 'block';
                cameraMsg.textContent = 'No se pudo activar la camara. Verifica permisos del navegador.';
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach((track) => track.stop());
                stream = null;
            }
        }

        function captureFace() {
            if (!video.videoWidth || !video.videoHeight) {
                cameraMsg.style.display = 'block';
                cameraMsg.textContent = 'La camara aun no esta lista. Intenta de nuevo en 1 segundo.';
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            faceCaptureInput.value = dataUrl;

            preview.src = dataUrl;
            preview.style.display = 'block';
            video.style.display = 'none';
            cameraMsg.style.display = 'none';

            captureBtn.disabled = true;
            retakeBtn.disabled = false;
            stopCamera();
        }

        function retakeFace() {
            faceCaptureInput.value = '';
            preview.src = '';
            preview.style.display = 'none';
            captureBtn.disabled = true;
            retakeBtn.disabled = true;
            cameraMsg.style.display = 'block';
            cameraMsg.textContent = 'Activa la camara y toma una foto frontal del rostro.';
        }

        startBtn.addEventListener('click', startCamera);
        captureBtn.addEventListener('click', captureFace);
        retakeBtn.addEventListener('click', retakeFace);
        roleSelect.addEventListener('change', updateSecurityFields);

        window.addEventListener('beforeunload', stopCamera);
        document.addEventListener('DOMContentLoaded', updateSecurityFields);
    </script>
</body>
</html>


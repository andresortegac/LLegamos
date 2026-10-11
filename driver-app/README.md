# Llegamos Conductor

Aplicación Android del conductor conectada a la API `/api/v1` de Llegamos.

## Funciones incluidas

- Acceso con token Sanctum por dispositivo.
- Token cifrado mediante Android KeyStore.
- Disponibilidad en línea/fuera de línea.
- GPS en primer y segundo plano con notificación visible.
- Rechazo de ubicaciones simuladas.
- Solicitudes cercanas, aceptar/rechazar, inicio mediante PIN y finalización.
- Estado de red, precisión y última señal GPS.

## Probar en un teléfono

1. Ejecutar Laravel accesible desde la red local: `php artisan serve --host=0.0.0.0 --port=8000`.
2. Instalar `releases/Llegamos-Conductor-1.0-debug.apk` en Android 6 o superior.
3. En "Servidor de Llegamos" escribir `http://IP-DEL-COMPUTADOR:8000/api/v1`.
4. Ingresar con un conductor verificado, activo y con correo confirmado.
5. Autorizar ubicación precisa y notificaciones, y pulsar "Conectarme".

La compilación debug sirve únicamente para pruebas internas. Para publicar se requiere HTTPS, dominio productivo, política de privacidad, clave privada de firma y Android App Bundle de release.

## Compilar

`npm run build`, `npx cap sync android` y luego `android\gradlew.bat assembleDebug` usando JDK 21 y Android SDK 35.
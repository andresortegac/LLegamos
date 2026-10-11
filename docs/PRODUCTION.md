# Operacion de produccion de Llegamos

## Requisitos externos

- Dominio y certificado TLS valido.
- PostgreSQL, Redis y almacenamiento de objetos con copias de seguridad.
- Cuenta SMTP transaccional.
- Comercio Wompi aprobado y secretos independientes para sandbox y produccion.
- Producto Wompi Pagos a terceros si se automatizaran retiros.
- Proveedor KYC/biometrico autorizado para Colombia.

## Despliegue

1. Copiar `.env.production.example` a `.env.production` fuera del repositorio y reemplazar todos los secretos.
2. Construir: `docker compose --env-file .env.production -f compose.production.yml build --pull`.
3. Aplicar migraciones: `docker compose --env-file .env.production -f compose.production.yml run --rm app php artisan migrate --force`.
4. Optimizar: `docker compose --env-file .env.production -f compose.production.yml run --rm app php artisan optimize`.
5. Levantar: `docker compose --env-file .env.production -f compose.production.yml up -d`.
6. Confirmar `GET /up` y `GET /health/ready` antes de habilitar trafico.

El proxy TLS debe terminar HTTPS delante de Nginx y permitir WebSocket en `/app/`. Nunca se deben copiar `.env`, documentos privados o respaldos dentro de la imagen.

## Procesos permanentes

Los servicios `worker`, `scheduler` y `reverb` deben permanecer activos. Antes de un despliegue, poner la app en mantenimiento, ejecutar migraciones compatibles hacia atras, reiniciar workers con `queue:restart` y retirar el mantenimiento solo después del health check.

## Respaldo y recuperacion

- PostgreSQL: respaldo cifrado diario y respaldo incremental/PITR administrado por el proveedor.
- Objetos privados: versionado y politica de retencion.
- Redis: no es fuente de verdad; puede reconstruirse.
- Probar restauracion en un ambiente aislado al menos mensualmente.
- Objetivos iniciales: RPO 24 horas y RTO 4 horas; reducirlos antes de una operacion de gran escala.

## Rotacion y respuesta

Rotar secretos SMTP, Wompi, VAPID, Reverb, base de datos y almacenamiento siguiendo un calendario. Revocar inmediatamente cualquier secreto expuesto, revisar auditoria, detener pagos si hay inconsistencia y conservar evidencia sin almacenar datos completos de tarjetas.
## Verificacion automatica y carga

Cada cambio se valida en GitHub Actions con auditoria de Composer/NPM, compilacion de la PWA y todas las pruebas PHP (`.github/workflows/ci.yml`).

Para un ensayo de carga seguro contra un ambiente de pruebas:

```bash
k6 run -e BASE_URL=https://staging.tu-dominio.com tests/load/smoke.js
```

El perfil sube hasta 25 usuarios virtuales y exige menos de 1% de errores y p95 inferior a 800 ms. No debe ejecutarse contra produccion sin ventana aprobada. Ajusta esos umbrales con metricas reales antes del lanzamiento.

El disco `PRIVATE_FILESYSTEM_DISK=s3` debe apuntar a un bucket privado, sin acceso publico, con cifrado, versionado, retencion y credenciales de minimo privilegio. Los documentos se descargan exclusivamente mediante rutas autenticadas de la aplicacion.
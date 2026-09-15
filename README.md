# Overlook Resort & Spa

Plataforma web de reservaciones hoteleras construida con **Laravel 13**, **Blade** y frontend **100% HTML + CSS + JavaScript Vanilla**.

## Stack

- PHP 8.4+
- Laravel 13
- SQLite (desarrollo) / MySQL o PostgreSQL (producción)
- Vite solo como bundler de assets (sin Tailwind, React, Vue, etc.)

## Inicio rápido

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

## Google OAuth

1. Crea credenciales OAuth 2.0 en [Google Cloud Console](https://console.cloud.google.com/).
2. Tipo de aplicación: **Web**.
3. URI de redirección autorizada:

```
http://localhost:8000/auth/google/callback
```

Origen autorizado de JavaScript:

```
http://localhost:8000
```

En desarrollo local abre la app siempre con `http://localhost:8000` (no uses `127.0.0.1`).

Si usas Windows y el intercambio de token falla por SSL, descarga el bundle de certificados:

```bash
curl -fsSL -o storage/app/cacert.pem https://curl.se/ca/cacert.pem
```

4. Configura `.env`:

```env
GOOGLE_CLIENT_ID=tu-client-id
GOOGLE_CLIENT_SECRET=tu-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

5. Visita `/login` y usa **Continuar con Google** o el formulario de correo y contraseña.

Las cuentas demo del seeder inician sesión con correo y contraseña. Los huéspedes también pueden **crear cuenta** en `/register`. Si olvidas la contraseña, usa `/forgot-password`.

### Correo (restablecer contraseña)

En local, `MAIL_MAILER=log` escribe el enlace en `storage/logs/laravel.log`. El correo usa la plantilla `resources/views/mail/auth/reset-password.blade.php` y sale desde `MAIL_FROM_ADDRESS`.

Para producción configura SMTP (o el mailer que inyecte Laravel Cloud):

```env
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.example.com
MAIL_PORT=465
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="reservaciones@tu-dominio.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Usa un remitente de un dominio que controles. `hello@example.com` no es válido para entrega real.

Los huéspedes nuevos de Google siguen creándose automáticamente en el callback de OAuth.

## Usuarios demo (seeder)

| Rol | Email | Password |
|-----|-------|----------|
| Admin | admin@overlook.test | password |
| Recepción | reception@overlook.test | password |
| Soporte | support@overlook.test | password |
| Huésped | guest@overlook.test | password |

## Configuración Overlook

Ver `config/overlook.php` y variables `OVERLOOK_*` en `.env`.

## Scheduler

```bash
php artisan schedule:work
```

Comando: `overlook:send-room-delivery-reminders`

En producción habilita el scheduler del entorno (Laravel Cloud: scheduler en el cluster de la app).

## Despliegue

La ruta recomendada es [Laravel Cloud](https://cloud.laravel.com/): conectar el repo, PHP 8.4+, build `composer install --no-dev && npm run build`, deploy `php artisan migrate --force`.

Checklist mínimo de entorno:

- `APP_URL` con HTTPS (la cámara de recepción lo requiere fuera de localhost)
- `APP_KEY`
- Base de datos MySQL o PostgreSQL (no SQLite de archivo local)
- `MAIL_*` SMTP real y `MAIL_FROM_ADDRESS` del dominio
- `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` del dominio público
- Scheduler para recordatorios de entrega de habitación

Este repositorio no incluye un stack Docker propio; no es necesario para Cloud.

## Arquitectura

- `app/Enums` — estados centralizados
- `app/Models` — Eloquent + relaciones
- `app/Services` — lógica de negocio
- `app/Policies` — autorización
- `resources/views` — Blade modular
- `resources/css` — CSS propio modular
- `resources/js` — JavaScript vanilla modular

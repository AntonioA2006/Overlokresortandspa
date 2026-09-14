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

5. Visita `/login` y usa **Continuar con Google**.

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

## Arquitectura

- `app/Enums` — estados centralizados
- `app/Models` — Eloquent + relaciones
- `app/Services` — lógica de negocio
- `app/Policies` — autorización
- `resources/views` — Blade modular
- `resources/css` — CSS propio modular
- `resources/js` — JavaScript vanilla modular

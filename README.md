# Artsmile — Dra. Andrea Vargas

Sitio de marca personal + plataforma de cursos (LMS) para la Dra. Andrea Vargas. Laravel 13 + Tailwind v4, bilingüe (ES/EN).

## Requisitos

- PHP 8.3+
- Composer
- Node.js + npm
- SQLite (o el motor que configures en `DB_CONNECTION`)

## Instalación

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
npm run build
```

Para desarrollo (servidor + worker de colas + Vite, todo junto):

```bash
composer run dev
```

## Acceso: administradora vs. estudiantes

**Todos usan el mismo formulario de login (`/login`).** No hay una URL de admin separada — lo que distingue a la administradora de un estudiante es el campo `is_admin` en la tabla `users`, y el sistema redirige a cada quien a su propia área automáticamente después de iniciar sesión:

- **Administradora** (`is_admin = true`) → redirige a `/admin` (panel de gestión de cursos, validaciones y compras).
- **Estudiante o visitante registrado** (`is_admin = false`) → redirige a `/perfil` (sus cursos, certificados e insignias).

Esto se resolvió porque antes **todo login redirigía a `/admin` sin importar el rol**, lo que exponía innecesariamente el panel administrativo a cualquier estudiante que iniciara sesión. Ver `App\Http\Controllers\Auth\AuthenticatedSessionController::store()`.

La navegación pública (`/`) solo muestra enlaces de **"Iniciar Sesión"** y **"Crear Cuenta"** — nunca menciona "admin" ni ofrece ningún atajo hacia el panel administrativo. La ruta `/admin` en sí está protegida por el Gate `admin` (`can:admin` middleware, ver `AppServiceProvider`), así que aunque alguien adivine la URL, solo entra si su cuenta tiene `is_admin = true`.

### Cuenta administradora (sembrada en `DatabaseSeeder`)

```
Email:    andrea@artsmile.co
Password: artsmile-2026
```

**Cambia esta contraseña antes de producción** — está en texto plano en el seeder.

### Cuentas de estudiante

Se crean libremente desde `/register`. No requieren aprobación para existir, pero **sí requieren validación de identidad** (diploma + cédula) antes de poder comprar un curso — ver la siguiente sección.

## Flujo de validación de identidad (IA)

1. El estudiante sube diploma + cédula en `/validacion`.
2. Se encola un job (`ProcessValidationRequest`) que llama a `AIValidationService`.
3. Si hay `GEMINI_API_KEY` en `.env`, se usa Gemini 1.5 Flash (visión) para verificar los documentos automáticamente.
4. **Sin esa clave**, el sistema cae a un modo de respaldo que siempre devuelve un puntaje de 0.65 (queda en estado "procesando", pendiente de revisión manual).
5. La administradora puede aprobar/rechazar manualmente en `/admin/validations` en cualquier momento, tenga o no IA configurada.

Importante: como la validación se procesa en una **cola**, necesitas un worker corriendo (`php artisan queue:work`, o `composer run dev` que ya lo incluye) para que esto no se quede atascado en estado "pendiente".

## Pagos

- **Bold**: si no configuras `BOLD_API_KEY`/`BOLD_API_BASE`/`BOLD_WEBHOOK_SECRET`, el sistema usa un flujo simulado local (útil para probar sin credenciales reales) que marca la compra como pagada automáticamente.
- **Transferencia bancaria**: aprobación manual desde `/admin/purchases`.

## Tests

```bash
php artisan test
```

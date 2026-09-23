# Artsmile — Dra. Andrea Vargas

Sitio de marca personal + plataforma de cursos (LMS) para la Dra. Andrea Vargas. Laravel 13 + Tailwind v4, bilingüe (ES/EN).

## Requisitos

- PHP 8.3+
- Composer
- Node.js + npm
- MySQL 8+

## Instalación

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Crea la base de datos y un usuario dedicado (no uses `root` directamente):

```sql
CREATE DATABASE andreavargasapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'andreavargasapp'@'localhost' IDENTIFIED BY 'tu-contraseña';
GRANT ALL PRIVILEGES ON andreavargasapp.* TO 'andreavargasapp'@'localhost';
FLUSH PRIVILEGES;
```

Configura `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env` con esos datos, y luego:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

**Toda la persistencia vive en MySQL** — no solo los datos de la app (cursos, usuarios, compras), sino también las sesiones (`SESSION_DRIVER=database`) y la caché (`CACHE_STORE=database`). No se usa SQLite en ningún ambiente que no sea el de tests.

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

## Despliegue

Planeado para **Latinoamérica Hosting**. Pendiente por resolver el día del despliegue:

- [ ] **Poner a correr el worker de colas de forma permanente.** Certificados (`GenerateCertificate`) y validación de identidad con IA (`ProcessValidationRequest`) se procesan en cola (`QUEUE_CONNECTION=database`), no al instante — sin un worker corriendo, esos jobs se quedan pendientes para siempre (ver sección de validación de identidad arriba). Localmente esto se resuelve con `php artisan queue:work`, pero en producción necesita quedar corriendo solo, sin que alguien lo deje abierto en una terminal.
  - Primero hay que revisar qué ofrece el panel de Latinoamérica Hosting:
    - **Si es hosting tipo VPS con acceso SSH** (proceso persistente posible): configurar `Supervisor` para mantener `php artisan queue:work --tries=3 --max-time=3600` corriendo y reiniciándose solo si se cae.
    - **Si es hosting compartido tipo cPanel** (sin procesos persistentes, solo Cron Jobs): agregar en cPanel → Cron Jobs una tarea que corra cada minuto:
      ```
      * * * * * cd /ruta/a/la/app && php artisan queue:work --stop-when-empty --tries=3 >> /dev/null 2>&1
      ```
      Esto procesa lo pendiente cada minuto en vez de dejar un proceso vivo — funciona igual de bien para este volumen de uso.
  - Verificar que quedó funcionando: completar un curso de prueba y confirmar que el certificado aparece en `/perfil` sin tener que correr nada manualmente (ver cómo se probó esto en desarrollo — completar lecciones, revisar la tabla `jobs`, y que se vacíe sola).
- [ ] Confirmar que `php artisan storage:link` se ejecutó en el servidor de producción (si no, los PDFs de certificados y las imágenes subidas por el admin no se van a poder descargar/mostrar).

## Tests

```bash
php artisan test
```

Los tests corren sobre SQLite en memoria (ver `phpunit.xml`), no contra el MySQL de desarrollo — así son rápidos y no requieren una base de datos real corriendo.

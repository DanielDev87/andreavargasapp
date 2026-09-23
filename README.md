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

Planeado para **Latinoamérica Hosting**, plan compartido con cPanel **sin acceso SSH** (confirmado con soporte — SSH solo viene en planes Empresarial/VPS, y decidimos no subir de plan). Repo privado en GitHub, dominio y SSL ya activos en `https://draandreavargas.art`.

### CI/CD (GitHub Actions)

Dos workflows en `.github/workflows/`:

- **`ci.yml`** — corre en cada push/PR a `main`: instala dependencias, corre `vendor/bin/pint --test` y `php artisan test`. No necesita ningún secreto, funciona tal cual con el repo en privado (GitHub da 2,000 minutos/mes gratis para repos privados).
- **`deploy.yml`** — corre en cada push a `main` (o manualmente desde la pestaña Actions). Primero reusa `ci.yml` como gate: si los tests fallan, no despliega. Si pasan:
  1. Compila todo en el runner de GitHub (`composer install --no-dev`, `npm run build`).
  2. Genera `index.php` y `deploy-hook.php` para `public_html/` a partir de las plantillas en `deploy/`, insertando las rutas absolutas reales del servidor (todo esto pasa **en GitHub, no en el servidor** — sin SSH no hay forma de ejecutar nada ahí mismo).
  3. Sube la app completa por FTPS a una carpeta **fuera** de `public_html/` (excluyendo `.env`, `storage/app/public` y cachés de runtime, para no pisar secretos ni contenido subido por usuarios).
  4. Sube por separado los assets compilados + los dos archivos generados a `public_html/`.
  5. Llama a `https://draandreavargas.art/deploy-hook.php?token=...` — ese script (protegido con un token largo) es el que sí corre código en el servidor: crea el symlink `public_html/storage` la primera vez (necesario para que las imágenes de cursos y certificados subidos DESPUÉS de cada deploy se sigan viendo), corre `migrate --force`, regenera caché de config/rutas/vistas, y `queue:restart`.

**Por qué la app vive fuera de `public_html/`:** soporte confirmó que el docroot del dominio principal es fijo en `public_html/` — no se puede apuntar directo a la carpeta `public/` de Laravel. La alternativa (subir TODO el código, incluyendo `vendor/` y el `.env`, directamente a `public_html/`) expondría el código fuente y las credenciales de la base de datos a cualquiera que adivine la URL. Por eso el código real va en una carpeta separada, y `public_html/` solo recibe los archivos que de verdad necesitan ser públicos.

**Secretos que hay que configurar en GitHub** (Settings → Secrets and variables → Actions, en el repo):

| Secreto | Valor |
|---|---|
| `FTP_SERVER` | Host FTP/SFTP que te da Latinoamérica Hosting |
| `FTP_USERNAME` | Usuario FTP |
| `FTP_PASSWORD` | Contraseña FTP |
| `FTP_APP_DIR` | Carpeta destino para la app completa, **tal como la ve el cliente FTP** (puede ser relativa, ej. `artsmile-app`, si tu FTP arranca en tu home) |
| `FTP_PUBLIC_HTML_DIR` | Carpeta destino para `public_html/`, tal como la ve el cliente FTP (normalmente `public_html`) |
| `APP_ABSOLUTE_PATH` | Ruta **absoluta real en el filesystem** a la carpeta de la app, ej. `/home/usuario/artsmile-app` — esto es lo que usa PHP internamente, puede ser un valor distinto a `FTP_APP_DIR` si tu FTP está encerrado ("chroot") en tu home |
| `PUBLIC_HTML_ABSOLUTE_PATH` | Ruta absoluta real a `public_html/`, ej. `/home/usuario/public_html` |
| `APP_DOMAIN` | `draandreavargas.art` |
| `DEPLOY_HOOK_TOKEN` | Token largo — generar con `openssl rand -hex 32` |

`FTP_APP_DIR`/`FTP_PUBLIC_HTML_DIR` vs. `APP_ABSOLUTE_PATH`/`PUBLIC_HTML_ABSOLUTE_PATH` suelen ser el mismo texto escrito distinto (uno relativo, uno absoluto) — pero si el primer deploy da error 500, ese es el sospechoso número uno: confirma la ruta absoluta real por File Manager (la muestra arriba al entrar a una carpeta) o pidiéndosela a soporte.

El mismo `DEPLOY_HOOK_TOKEN` también debe quedar en el `.env` real del servidor — ver `deploy/env.production.example`.

### Pendiente por resolver el día del despliegue

- [x] **Docroot en cPanel: confirmado con soporte que no se puede mover.** Resuelto arriba — la app vive fuera de `public_html/`, y el pipeline arma `public_html/` desde cero en cada deploy sin ningún paso manual.
- [ ] **Conseguir los valores exactos de `FTP_SERVER`/`FTP_APP_DIR`/`FTP_PUBLIC_HTML_DIR`/rutas absolutas.** cPanel → Archivos → Administrador de Archivos (para ver las rutas reales), y cPanel → Archivos → Cuentas FTP (para el host/usuario/contraseña FTP, o usar directamente las credenciales de la cuenta cPanel si no hay una cuenta FTP separada creada).
- [ ] **Poner a correr el worker de colas.** Sin SSH no hay Supervisor disponible — usar `deploy/crontab-queue-worker.txt`: cPanel → Avanzado → Cron Jobs (se configura por la interfaz web, no necesita SSH), una tarea cada minuto que procesa lo pendiente y termina sola.
  - Verificar que quedó funcionando: completar un curso de prueba y confirmar que el certificado aparece en `/perfil` sin correr nada manualmente.
- [ ] **Crear el `.env` de producción a mano en el servidor** (nunca se sube por el pipeline, ver `.gitignore`). Sin SSH, esto se hace por el Administrador de Archivos de cPanel (crear el archivo `.env` directamente ahí, o subirlo por FTP a la carpeta de `FTP_APP_DIR` una sola vez — el pipeline nunca lo toca después). Usa `deploy/env.production.example` como plantilla, ya con `APP_URL`, correo SMTP y `DEPLOY_HOOK_TOKEN` llenos. Después de crearlo hay que correr `php artisan key:generate` — como tampoco hay SSH para eso, genera la key localmente con `php artisan key:generate --show` y pégala directo en el `.env` del servidor.
  1. Crear la base de datos en cPanel → **Bases de datos MySQL** (el asistente, no SQL directo). cPanel antepone tu usuario cPanel al nombre de la BD y del usuario (ej. `usuario_artsmile`).
  2. **Llaves de Bold de producción** siguen pendientes — las que usamos en desarrollo son de prueba/sandbox. Hacen falta las de producción de la cuenta Bold (dashboard → llaves de integración) y configurar ahí el webhook de producción apuntando a `https://draandreavargas.art/webhooks/bold`.

## Tests

```bash
php artisan test
```

Los tests corren sobre SQLite en memoria (ver `phpunit.xml`), no contra el MySQL de desarrollo — así son rápidos y no requieren una base de datos real corriendo.

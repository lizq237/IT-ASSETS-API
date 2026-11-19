# IT Assets Management — API REST

**Backend de un sistema de gestión de activos tecnológicos y tickets de soporte, diseñado con arquitectura multi-tenant.**

> **Estado:** proyecto académico en desarrollo · **Facultad de Informática, UAS** · **Líder de proyecto:** Lizeth Quiroz Urias

La API conecta el panel administrativo en React con la aplicación Android y centraliza la autenticación, los permisos y las reglas del inventario. Cada activo y usuario pertenece a una organización; los tickets se almacenan en una base documental independiente.

**Repositorios del sistema:** [Web y guía general](https://github.com/lizq237/IT-ASSETS-WEB) · **API (este repositorio)** · [Móvil Android](https://github.com/lizq237/IT-ASSETS-MOVIL)

## Tecnologías

| Componente | Tecnología |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12, API REST |
| Autenticación | Laravel Sanctum, tokens Bearer |
| Datos relacionales | PostgreSQL 16: organizaciones, usuarios, catálogos, activos |
| Datos documentales | MongoDB 7: tickets de soporte |
| Infraestructura local | Docker y Docker Compose (en el repositorio Web) |
| Pruebas | PHPUnit / Artisan; clientes HTTP como Thunder Client |

> **Redis:** el contenedor existe en `IT-ASSETS-WEB/docker-compose.yml`; el uso de caché con Redis es una integración prevista, no una función implementada y verificada en esta API.

## Arquitectura y permisos

```mermaid
flowchart TD
    W[Panel web React] --> A[API Laravel + Sanctum]
    M[Android Java + Retrofit] --> A
    A --> P[(PostgreSQL)]
    A --> T[(MongoDB: tickets)]
```

- **`super_admin`:** administración de organizaciones y alta de tenants.
- **`admin_ti`:** gestión del inventario, catálogos y usuarios de su organización.
- **`tecnico`:** consulta de activos y actualización de estado de equipos asignados (según permisos).
- **`responsable`:** cuenta con acceso autenticado para consultar equipos propios y levantar tickets.

El modelo `Asset` emplea un `TenantScope` y varios controladores filtran por `organization_id`. La creación de tickets verifica que el activo pertenezca tanto a la organización como al usuario autenticado. Estas medidas deben seguir auditándose antes de un despliegue público.

## Inicio rápido en local

### 1. Requisitos

- Git, Composer y **PHP 8.2+**.
- Extensiones PHP `pdo_pgsql`, `pgsql` y `mongodb` habilitadas.
- Docker Desktop con Docker Compose; puertos `5432`, `27017` y `8000` disponibles.

Verifica las extensiones con `php -m`. En Windows/XAMPP, habilítalas en el `php.ini` que use **el PHP de tu terminal** (puede diferir del de Apache).

### 2. Clonar API e infraestructura

Desde un directorio común:

```bash
git clone -b developer https://github.com/lizq237/IT-ASSETS-WEB.git
git clone -b developer https://github.com/lizq237/IT-ASSETS-API.git
```

Inicia las bases de datos desde el repositorio **Web**, que contiene el `docker-compose.yml`:

```bash
cd IT-ASSETS-WEB
docker compose up -d
docker compose ps
```

Ese archivo levanta PostgreSQL, MongoDB y Redis. **La API no necesita Redis para las funciones descritas aquí.**

### 3. Instalar y configurar Laravel

Abre otra terminal en `IT-ASSETS-API`:

```bash
cd IT-ASSETS-API
composer install
```

Copia `.env.example` a `.env` (PowerShell: `Copy-Item .env.example .env`; Bash: `cp .env.example .env`).

En `.env`, configura el acceso a los contenedores locales:

```dotenv
APP_NAME="IT Assets"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inventario_fic
DB_USERNAME=root
DB_PASSWORD=secretpassword

MONGODB_URI="mongodb://root:secretpassword@127.0.0.1:27017/?authSource=admin"
MONGODB_DATABASE=sysbase_tickets
```

**Solo para desarrollo local:** los valores anteriores coinciden con el Compose actual. No expongas estas contraseñas, la cuenta de prueba ni `APP_DEBUG=true` en producción. No subas tu archivo `.env` a GitHub.

Ejecuta:

```bash
php artisan key:generate
php artisan optimize:clear
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

La API queda disponible en **http://127.0.0.1:8000/api**. Puedes listar las rutas con `php artisan route:list --path=api`.

### 4. Usuarios de demostración

Los seeders actuales crean organizaciones, un superadministrador y algunos administradores TI de ejemplo. Para comprobar el login en un entorno **local recién creado**:

| Rol | Correo | Contraseña local |
| --- | --- | --- |
| `super_admin` | `super@sysbase.com` | `password123` |
| `admin_ti` (organización de prueba) | `admin@barca.com` | `password123` |

Estos son **datos ficticios de desarrollo**. El superadministrador no pertenece a una organización, por lo que no sirve como usuario de prueba para levantar tickets. **Los seeders no crean un inventario completo ni un usuario móvil con equipos asignados.**

## Endpoints principales

Todas las rutas están bajo el prefijo `/api`. Excepto el login, requieren `Authorization: Bearer <token>`.

| Método | Ruta | Descripción / permiso |
| --- | --- | --- |
| POST | `/login` | Login público; retorna token Sanctum |
| POST | `/logout` | Revoca el token actual |
| GET | `/me` | Perfil autenticado |
| GET | `/organizaciones` | Lista de organizaciones; `super_admin` |
| POST | `/register-tenant` | Alta de organización; `super_admin` |
| GET, POST | `/categories` | Consultar / crear categorías (`POST`: `admin_ti`) |
| GET, POST | `/brands` | Consultar / crear marcas (`POST`: `admin_ti`) |
| GET, POST | `/locations` | Consultar / crear ubicaciones (`POST`: `admin_ti`) |
| GET, POST | `/assets` | Consultar / registrar activos (`POST`: `admin_ti`) |
| POST | `/assets/{id}/assign` | Asignar un equipo; `admin_ti` |
| PATCH | `/assets/{id}/status` | Actualizar estado; `admin_ti` o `tecnico` autorizado |
| GET, POST | `/users` | Usuarios de la organización; `admin_ti` |
| GET | `/employee/assets` | Activos asignados a la cuenta autenticada |
| POST | `/employee/tickets` | Crear ticket sobre activo asignado |
| GET | `/tickets/{id}/evidence` | Obtener evidencia si el usuario tiene acceso por organización |

Existen más rutas `GET/PUT/DELETE` para catálogos y activos. Consulta el código vigente en [`routes/api.php`](https://github.com/lizq237/IT-ASSETS-API/blob/developer/routes/api.php).

### Ejemplo: iniciar sesión

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@barca.com","password":"password123","device_name":"web"}'
```

La respuesta contiene `access_token`, `token_type` y `user`. Usa ese token en peticiones posteriores:

```bash
curl http://127.0.0.1:8000/api/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TU_TOKEN"
```

### Ejemplo: crear empleado y asignarle un equipo

Con el token de `admin_ti`, registra un empleado de **la misma organización**:

```http
POST /api/users
Authorization: Bearer TU_TOKEN
Content-Type: application/json

{
  "name": "Usuario Demo",
  "email": "empleado-demo@example.com",
  "password": "cambia-esta-clave",
  "role": "responsable"
}
```

Desde el panel web (o la API), crea categoría y equipo con `status` = `activo` y asígnalo a este usuario. `POST /api/assets` requiere `category_id`, `code`, `model` y `status`; `POST /api/assets/{id}/assign` recibe `user_id`.

Una vez que el usuario tenga un activo asignado, inicia sesión con su cuenta y consulta:

```http
GET /api/employee/assets
Authorization: Bearer TOKEN_DEL_EMPLEADO
```

Esa ruta devuelve una **lista JSON** de los equipos asignados.

### Ejemplo: levantar un ticket

Con un usuario que ya tenga el activo asignado:

```bash
curl -X POST http://127.0.0.1:8000/api/employee/tickets \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_DEL_EMPLEADO" \
  -F "asset_id=ID_DE_UN_ACTIVO_ASIGNADO" \
  -F "title=La computadora no enciende" \
  -F "description=No responde al presionar el botón de encendido" \
  -F "priority=alta"
```

Puedes agregar `-F "image=@/ruta/a/foto.png"` para incluir evidencia **JPEG o PNG hasta 5 MB**. Los tickets se guardan en MongoDB con estado inicial `abierto`; el archivo se guarda en el almacenamiento local privado de Laravel.

**Validaciones relevantes:** `401` sin sesión, `403` por permisos u organización ausente y `422` cuando el equipo no está asignado al usuario o los datos no cumplen las reglas.

## Verificación y pruebas

Desde `IT-ASSETS-API`:

```bash
php artisan route:list --path=api
php artisan test
```

Los tests de ejemplo existen en el repositorio, pero **no se presenta aquí una suite automatizada completa ni resultados de CI verificados**. Para evaluar el aislamiento multi-tenant, prueba con dos organizaciones y comprueba que los usuarios solo trabajen con datos autorizados.

## Solución de problemas

- **`could not find driver`:** verifica `pdo_pgsql` y que `php -m` muestre las extensiones en la terminal actual.
- **`Class MongoDB...` o errores de conexión MongoDB:** habilita la extensión `mongodb` compatible con tu versión de PHP y revisa `MONGODB_URI`.
- **Conexión rechazada a bases de datos:** ejecuta `docker compose ps` desde `IT-ASSETS-WEB` y comprueba puertos y credenciales.
- **La API usa SQLite inesperadamente:** cambia `DB_CONNECTION=pgsql` en `.env` y ejecuta `php artisan optimize:clear`.
- **Inventario vacío:** `migrate --seed` crea organizaciones y usuarios, no equipos completos; agrégalos en el panel web.
- **Android no conecta:** el emulador usa `10.0.2.2:8000` para llegar a la API del equipo anfitrión (véase el README móvil).

## Estado y siguientes pasos

La API funciona como base del proyecto académico y continúa evolucionando. Están pendientes la integración funcional de Redis, la ampliación del módulo de tickets, datos ficticios completos para demostración y más pruebas automatizadas.

Para montar el sistema completo, empieza por la [guía general del repositorio Web](https://github.com/lizq237/IT-ASSETS-WEB).

---

**Equipo:** proyecto colaborativo de seis integrantes, desarrollado bajo Scrum y coordinado por Lizeth Quiroz Urias.

# BoardLog
Aplicación para registrar partidas de juegos de mesa con roles *socio* y *superadmin*. Hecha con ChatGPT5 para probar su capacidad.
- Gráfica pública (Chart.js) con filtros por fecha.
- Área privada para socios (crear/editar/borrar juegos/partidas).
- Superadmin: todo lo anterior + gestión de usuarios + auditoría.

## Despliegue rápido con Docker compose 
```yaml
services:
  web:
    image: jmprof/boardlog:latest
    ports:
      - "8080:80"
    environment:
      - APP_URL=http://localhost:8080
      - DB_HOST=db
      - DB_NAME=boardlog
      - DB_USER=boardlog
      - DB_PASS=boardlog
      - ADMIN_USERNAME=admin
      - ADMIN_PASSWORD=admin
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: boardlog
      MYSQL_USER: boardlog
      MYSQL_PASSWORD: boardlog
      MYSQL_ROOT_PASSWORD: root
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```
Credenciales por defecto: **admin/admin** (cambia la contraseña posteriormente).

## Contraseña del superadmin por variable de entorno
En `docker-compose.yml` puedes definir:
```yaml
environment:
  ADMIN_USERNAME: admin
  ADMIN_PASSWORD: admin
```
La app sincroniza esa contraseña **al primer arranque** (y la volverá a sincronizar si cambias el valor en `docker-compose.yml`). 
Se guarda un hash en `config/.admin_pw_hash` para no sobreescribir cambios si no cambias la variable.

# Estructura y características

## 1) Resumen funcional

* Pública:

    * Página de inicio con gráfica (Chart.js) que muestra la media de jugadores por juego, con filtros de fecha.

* Socios (login requerido):

    * Juegos: listar, crear, editar y borrar (duro) cualquier juego.

    * Partidas: listar, crear, editar y borrar (duro) cualquier partida.

    * Mi cuenta: cambiar su propia contraseña (requiere contraseña actual).

* Superadmin además:

    * Usuarios: crear usuarios, resetear contraseñas de otros usuarios.

    * Ajustes: cambiar el título del sitio (se guarda en BD).

    * Auditoría: ver y purgar historial.

* Idiomas/zonas:

    * Interfaz en español.

    * Fechas en formato DD/MM/AAAA (y DD/MM/AAAA HH:mm en auditoría).

    * Zona horaria: Europe/Madrid.

* Tema visual:

    * Por defecto modo claro.

    * Botón en la barra de navegación, muestra la acción disponible:

        * 🌙 → pasar a oscuro.

        * ☀️ → volver a claro.

    * Persistencia en localStorage y autodetección si no hay preferencia guardada.

## 2) Arquitectura y stack

* Frontend: HTML + Bootstrap 5.3 + Chart.js.

* Backend: PHP 8.2 con PDO.

* Base de datos: MySQL 8.0.

* Despliegue: Docker + Docker Compose.

Estructura de carpetas
```
app/                # Código PHP (auth, db, modelos)
config/             # Configuración
public/             # DocumentRoot (Apache). HTML/PHP accesible vía web
  admin/            # Páginas del superadmin
  games/            # CRUD de juegos
  plays/            # CRUD de partidas
  account/          # “Mi cuenta” (cambio de contraseña)
  includes/         # header.php, footer.php, helpers.php, .htaccess
  index.php         # Portada con gráfica
  login.php         # Login (procesa POST antes de renderizar)
  logout.php        # Cierre de sesión
  graph_data.php    # Endpoint JSON de medias para Chart.js
Dockerfile          # Imagen web (php:8.2-apache)
docker-compose.yml  # Servicios web + db (y variables de entorno)
```

## 3) Configuración
### Variables principales (config/config.php)

* APP_NAME: nombre del sitio (por defecto “BoardLog”, editable en Ajustes).

* APP_URL: opcional, usada para enlaces absolutos (puede omitirse si se usa siempre la URL del servidor).

* TIMEZONE: Europe/Madrid.

* DB_*: credenciales de la base de datos.

* Sesiones y cookies seguras (SESSION_NAME, COOKIE_SECURE, etc.).

* CSRF_TOKEN_KEY: clave secreta para tokens CSRF.

### Variables de entorno en Docker

* DB_HOST, DB_NAME, DB_USER, DB_PASS.

* ADMIN_USERNAME, ADMIN_PASSWORD: se usan solo al primer arranque para crear el superadmin. Después se ignoran.

## 4) Base de datos
### Tablas

* users: id, username, password_hash, role(socio|superadmin), created_at.

* games: id, name, name_norm, created_at, updated_at.

* plays: id, game_id, players_count (1–100), played_at, created_at, updated_at.

* audit_logs: registro de todas las acciones (create, update, delete, settings_update).

* settings: pares clave/valor (app_name, admin_bootstrap_sig).

### Notas

* No existe deleted_at: los borrados son duros.

* Al borrar un juego o partida se registra en auditoría el nombre (no solo el ID).

## 5) Autenticación y sesiones

* Login con usuario y contraseña (password_hash / password_verify).

* Roles: socio y superadmin.

* Variables de entorno iniciales crean el superadmin al primer arranque.

* Cambio de contraseña:

    * Cualquier usuario desde Mi cuenta.

    * El superadmin puede resetear las contraseñas de otros desde el panel.

* Seguridad:

    *  CSRF: todos los formularios POST llevan token.

    * Sesiones seguras: regeneración de ID, cookies con HttpOnly/SameSite.

    * SQL seguro: consultas preparadas con PDO.

## 6) Vistas principales

* Inicio (index.php):

    * Filtro de fechas.

    * Gráfica de media de jugadores por juego (Chart.js).

* Juegos (games/):

    * Tabla con columnas: Nombre, Creado, Acciones.

    * Cualquier socio puede editar/borrar cualquier juego.

* Partidas (plays/):

    * Tabla con columnas: Fecha, Juego, Jugadores, Acciones.

    * Cualquier socio puede editar/borrar cualquier partida.

* Mi cuenta:

    * Cambio de contraseña del propio usuario.

* Admin:

    * Usuarios: crear, resetear contraseñas.

    * Ajustes: cambiar título del sitio.

    * Auditoría: consultar y purgar.

## 7) Auditoría

Cada acción se registra con:

* Tabla afectada.

* Acción (create/update/delete/settings_update).

* Usuario responsable.

* Fecha y hora.

* Detalle en JSON (ej. { "name": "Catan" } al borrar un juego).

El superadmin puede purgar toda la tabla desde el panel.

## 8) Tema claro/oscuro

* Botón en la esquina derecha de la barra de navegación.

* Icono indica la acción disponible (no el estado actual).

* Guarda preferencia en localStorage.

* Si no hay preferencia, autodetecta la del sistema (prefers-color-scheme).

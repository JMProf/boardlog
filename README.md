# BoardLog
Aplicación para registrar partidas de juegos de mesa con roles *socio* y *superadmin*. Hecha con ChatGPT5 para probar su capacidad.
- Gráfica pública (Chart.js) con filtros por fecha.
- Área privada para socios (crear/editar/borrar juegos/partidas).
- Superadmin: todo lo anterior + gestión de usuarios + auditoría.

## Docker compose 
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

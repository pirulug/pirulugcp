---
trigger: always_on
---

# Reglas para Archivos SQL (Estilo WordPress / Evan)

Este documento define los estándares obligatorios para la creación y modificación de archivos ".sql" en la base de datos de la aplicación.

## 1. Consulta y Modificación de la Base de Datos
- **Revisión Obligatoria:** Cuando no se conozca la estructura de la base de datos, o al intentar realizar cualquier consulta o modificación, es obligatorio revisar el contenido de la carpeta "database" para validar el esquema actual.

## 2. Nomenclatura de Tablas
- **Formato:** Los nombres de las tablas deben ser siempre en plural y usar "snake_case".
  - Correcto: "users", "roles", "options".
  - Incorrecto: "user", "Role", "tbl_options".
- **Tablas de Metadatos:** Para extender atributos de una entidad sin modificar la tabla principal, se debe crear una tabla secundaria usando el sufijo "meta".
  - Ejemplo: "usermeta" (para "users"), "rolemeta" (para "roles").

## 3. Nomenclatura de Columnas
- **Prefijo de Tabla:** Todas las columnas de una tabla (incluyendo la clave primaria) deben estar prefijadas con el nombre en singular de la tabla a la que pertenecen.
  - Ejemplo para tabla "users": "user_id", "user_login", "user_password", "user_email".
  - Ejemplo para tabla "options": "option_id", "option_key", "option_value".
- **Clave Primaria:** Debe llamarse siempre "<tabla_singular>_id" con el tipo de datos "INT AUTO_INCREMENT PRIMARY KEY".
  - Ejemplo: "user_id" para "users", "role_id" para "roles".
- **Estructura Estándar de Tablas Meta:** Las tablas de metadatos deben seguir estrictamente la siguiente estructura de columnas:
  - "<tabla_singular>meta_id INT AUTO_INCREMENT PRIMARY KEY"
  - "<tabla_singular>_id INT NULL" (clave foránea a la tabla principal)
  - "<tabla_singular>meta_key VARCHAR(150) NOT NULL"
  - "<tabla_singular>meta_value TEXT NULL"

## 4. Sintaxis SQL y Palabras Clave
- **Mayúsculas Obligatorias:** Todas las palabras clave de SQL, tipos de datos y atributos de columna deben escribirse estrictamente en MAYÚSCULAS.
  - Palabras clave: "CREATE TABLE", "INSERT INTO", "VALUES", "UNIQUE KEY", "FOREIGN KEY", "REFERENCES", "ON DELETE", "ON UPDATE", "DEFAULT", "NULL", "NOT NULL".
  - Tipos de datos: "INT", "VARCHAR", "TEXT", "TINYINT", "DATETIME".
  - Atributos: "AUTO_INCREMENT PRIMARY KEY", "DEFAULT NULL", "DEFAULT CURRENT_TIMESTAMP", "ON UPDATE CURRENT_TIMESTAMP".
- **Motor de Almacenamiento y Charset:** Al finalizar la creación de cualquier tabla, se debe especificar siempre el motor "InnoDB" y el conjunto de caracteres "utf8mb4".
  - Sintaxis: "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"

## 5. Claves Foráneas e Índices
- **Restricciones de Integridad:** Las claves foráneas deben declararse explícitamente al final de la definición de la tabla y usar cascada en eliminación y actualización.
  - Sintaxis: "FOREIGN KEY (<tabla_singular>_id) REFERENCES <tabla_plural>(<tabla_singular>_id) ON DELETE CASCADE ON UPDATE CASCADE"
- **Unicidad en Metadatos:** Las tablas meta deben incluir una clave única compuesta para evitar duplicidad de una clave ("key") para un mismo registro.
  - Sintaxis: "UNIQUE KEY uniq_<tabla_singular>_meta (<tabla_singular>_id, <tabla_singular>meta_key)"

## 6. Columnas de Auditoría Temporal
- Para tablas que requieran rastreo de creación y edición, se deben utilizar columnas con los siguientes nombres y configuraciones estándar:
  - "<tabla_singular>_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP"
  - "<tabla_singular>_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"

## 7. Formateo Visual del Código
- **Indentación:** Usar estrictamente 2 espacios para indentar la definición de las columnas dentro del bloque "CREATE TABLE".
- **Separadores de Tablas:** Cada definición de tabla debe estar precedida por un encabezado estandarizado compuesto por 57 caracteres "=" en un bloque de comentario.
  - Ejemplo:
    ```sql
    -- =========================================================
    -- TABLA: USERS
    -- =========================================================
    CREATE TABLE users (
      user_id INT AUTO_INCREMENT PRIMARY KEY,
      user_login VARCHAR(255) NULL UNIQUE,
      user_password VARCHAR(255) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ```
- **Secciones de Datos:** Las secciones de inserciones de datos iniciales o datos de prueba deben separarse visualmente usando la misma estructura.
  - Ejemplo:
    ```sql
    -- =========================================================
    -- DATOS INICIALES
    -- =========================================================
    ```
- **Inserciones de Datos (INSERT INTO):**
  - Especificar siempre explícitamente las columnas en el "INSERT INTO".
  - Utilizar comillas simples (') para los valores de cadena (estándar SQL para literales).
  - Ejemplo:
    ```sql
    INSERT INTO roles (role_id, role_name, role_description) VALUES 
    (1, 'Administrador', 'Usuario con acceso administrativo');
    ```

# Instrucciones para la configuración y nomenclatura de la base de datos


## 1. Configuración de la base de datos

La información de conexión a la base de datos se gestiona exclusivamente a través de variables de entorno. **Todas son obligatorias** y la aplicación no funcionará si falta alguna:

### Variables de entorno requeridas

- `DB_HOST`: Host de la base de datos (ejemplo: `localhost` en local, `sqlXXX.infinityfree.com` en InfinityFree)
- `DB_NAME`: Nombre de la base de datos
- `DB_USER`: Usuario de la base de datos
- `DB_PASS`: Contraseña de la base de datos

Debes definir estas variables en un archivo `.env` (no lo subas al repositorio) o configurarlas directamente en el panel de tu hosting. Si alguna falta, la aplicación lanzará un error y no se conectará a la base de datos.

Ejemplo de archivo `.env`:

```
DB_HOST=localhost
DB_NAME=biblioteca_digital
DB_USER=root
DB_PASS=
```

Para InfinityFree, reemplaza los valores así:

```
DB_HOST=sqlXXX.infinityfree.com
DB_NAME=epiz_XXXXXXX_biblioteca
DB_USER=epiz_XXXXXXX
DB_PASS=tu_contraseña
```

## 2. Nomenclatura para trabajos relacionados a la base de datos

- **Tablas**: Usa nombres en minúsculas y en plural si almacenan colecciones (ejemplo: `usuarios`, `libros`, `prestamos`).
- **Campos**: Usa minúsculas y guiones bajos para separar palabras (ejemplo: `fecha_registro`, `id_usuario`).
- **Llaves primarias**: Siempre `id` o `id_<tabla>` si es clave compuesta.
- **Llaves foráneas**: `<tabla>_id` (ejemplo: `usuario_id`, `libro_id`).
- **Índices**: Prefijo `idx_` seguido del nombre de la tabla y campo (ejemplo: `idx_usuarios_email`).
- **Vistas**: Prefijo `vw_` seguido de la descripción (ejemplo: `vw_libros_disponibles`).
- **Procedimientos/Funciones**: Prefijo `sp_` o `fn_` seguido de la acción y entidad (ejemplo: `sp_insertar_prestamo`).

## 3. Exportar e importar la base de datos

- Para exportar: Usa phpMyAdmin y selecciona "Exportar" en tu base de datos local.
- Para importar: Usa phpMyAdmin en InfinityFree y selecciona "Importar".

## 4. Recomendaciones
- No subas archivos `.env` ni respaldos de la base de datos al repositorio.
- Actualiza este README si cambias la estructura o reglas de la base de datos.

---

**Ejemplo de archivo `.env`**

```
DB_HOST=sqlXXX.infinityfree.com
DB_NAME=epiz_XXXXXXX_biblioteca
DB_USER=epiz_XXXXXXX
DB_PASS=tu_contraseña
```

# 📚 Sistema de Biblioteca Digital

**Universidad Tecnológica de Panamá**  
**Facultad de Sistemas Computacionales**  
**Ingeniería de Software - Proyecto Semestral**

## 📋 Descripción

Sistema web para la gestión completa de una biblioteca digital universitaria. Permite la administración de libros, usuarios, reservas, préstamos y generación de reportes.

## ✨ Características

- **3 Roles**: Administrador, Bibliotecario, Estudiante
- **Gestión CRUD** de libros, usuarios y categorías
- **Sistema de reservas** con control de inventario
- **Búsqueda pública** sin autenticación
- **Subida de imágenes** con generación de thumbnails
- **Reportes en Excel** de libros y reservas
- **Registro público** para estudiantes

## 🛠️ Stack Tecnológico

- **Backend**: PHP (82.1%)
- **Frontend**: HTML, CSS (17.9%), JavaScript
- **Base de Datos**: MySQL
- **Servidor**: Apache
- **Contenedorización**: Docker (opcional)

## 🚀 Instalación Rápida

### 1. Clonar repositorio
```bash
git clone https://github.com/luzdl/biblioteca-proyectofinal.git
cd biblioteca-proyectofinal
```
### 2. Configurar Base de Datos
```bash
mysql -u root -p -e "CREATE DATABASE biblioteca_digital;"
mysql -u root -p biblioteca_digital < biblioteca_digital.sql
```
### 3. Configurar Entorno
```bash
cp .env.example .env
# Editar .env con tus credenciales de BD
```
### 4. Configurar servidor web
Apuntar DocumentRoot a carpeta public/
Habilitar mod_rewrite (Apache)

### 5. Acceder al sistema
URL: http://localhost/biblioteca-proyectofinal/public

Admin: admin / root2514

Bibliotecario: biblio / password

## 📁 Estructura del Proyecto
```bash
biblioteca-proyectofinal/
├── src/                    # Código fuente PHP
├── public/                 # Archivos públicos
│   ├── css/               # Estilos
│   ├── js/                # Scripts
│   ├── img/               # Imágenes
│   └── index.php          # Entrada principal
├── config/                 # Configuraciones
├── scripts/                # Utilidades
├── sql/                   # Scripts SQL
├── .env.example           # Variables de entorno
├── Dockerfile            # Config Docker
└── biblioteca_digital.sql # Esquema BD
```
## 📊 Base de Datos
Tablas principales:
- usuarios - Todos los usuarios del sistema

- carreras - Catálogo de carreras

- categorias_libros - Categorías de libros

- roles - Roles del sistema (RBAC)

- usuario_roles - Relación usuarios-roles

- uploads - Archivos subidos (imágenes)

### Diagrama de Relaciones Simplificado
``` bash
usuarios → carreras (pertenece a)
usuarios → uploads (tiene imagen)
usuarios ↔ roles (muchos a muchos)
```

## 👥 Roles y Permisos
-----------------------------------------------
Rol|Permisos
--------------------------------------
Administrador	|Gestión completa del sistema, usuarios, reportes
-----------------------------------------------------
Bibliotecario|	Gestión de libros, reservas, devoluciones
--------------------------------------------------------
Estudiante|	Consulta catálogo, reserva libros, solicitudes
---------------------------------------------------------

## 🐳 Docker (Opcional)
``` bash
# Construir y ejecutar
docker build -t biblioteca-digital .
docker run -p 8080:80 biblioteca-digital
```

## 📄 Documentación
- Casos de Uso: 21 casos documentados

- Diagramas UML: Casos de uso, secuencia, actividad

- Especificaciones: Requisitos funcionales/no funcionales

- Manual de instalación: Guía completa

## 🤝 Contribuir
1. Fork del repositorio

2. Crear rama (git checkout -b feature/nueva)

3. Commit cambios (git commit -m 'Add feature')

4. Push a la rama (git push origin feature/nueva)

5. Abrir Pull Request

## 👨‍🎓 Equipo
- José Bustamante (8-1011-1717)

- Luz De León (8-1020-247)

- María Ferrer (20-70-7664)

- Abigail Koo (8-997-974)

Facilitador: Irina Fong

Grupo: 1SF131

Semestre: Segundo 2025

Proyecto académico - Universidad Tecnológica de Panamá

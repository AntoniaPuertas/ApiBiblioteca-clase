# ApiBiblioteca

ApiBiblioteca es una API RESTful basada en PHP para gestionar la colección de libros y las cuentas de usuario de una biblioteca. Proporciona puntos finales para realizar operaciones CRUD en libros y maneja la autenticación y gestión de usuarios a través de una interfaz administrativa.

## Tabla de Contenidos

- [Características](#características)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Instalación](#instalación)
- [Documentación de la API](#documentación-de-la-api)
  - [URL Base](#url-base)
  - [Autenticación](#autenticación)
  - [Endpoints de Libros](#endpoints-de-libros)
    - [Obtener Todos los Libros](#obtener-todos-los-libros)
    - [Obtener Libro por ID](#obtener-libro-por-id)
    - [Crear Libro](#crear-libro)
    - [Actualizar Libro](#actualizar-libro)
    - [Eliminar Libro](#eliminar-libro)
- [Panel de Administración](#panel-de-administración)
- [Contribución](#contribución)
- [Licencia](#licencia)

## Características

- **Gestión de Libros**: Crear, leer, actualizar y eliminar registros de libros.
- **Carga de Imágenes**: Maneja la carga de imágenes de portada de libros.
- **Autenticación de Usuarios**: Inicio de sesión, registro y recuperación de contraseña seguros para usuarios.
- **Interfaz de Administración**: Interfaz web para tareas administrativas.
- **Integración con Base de Datos**: Base de datos MySQL para la persistencia de datos.
- **Notificaciones por Correo Electrónico**: Envío de correos electrónicos (simulado) para verificación de cuenta y recuperación de contraseña.

## Estructura del Proyecto

```
ApiBiblioteca/
├── api/             # Punto de entrada principal de la API
├── admin/           # Archivos de la interfaz administrativa
├── config/          # Configuración de la base de datos y del correo
├── controllers/     # Lógica de negocio para los endpoints de la API (LibroController, UsuarioController)
├── data/            # Objetos de Acceso a Datos (LibroDB, UsuarioDB) y librería PHPMailer
├── img/             # Imágenes de portada de libros
├── js/              # Archivos JavaScript globales
├── css/             # Archivos CSS globales
├── .htaccess        # Configuración de Apache para reescritura de URL
├── index.html       # Página de aterrizaje de marcador de posición
└── README.md        # Este archivo
```

## Instalación

1.  **Clonar el repositorio:**
    ```bash
    git clone <repository_url>
    cd ApiBiblioteca
    ```

2.  **Configuración de la Base de Datos:**
    *   Crea una base de datos MySQL (ej. `apibiblioteca`).
    *   Importa el esquema de la base de datos (necesitarás crearlo basándote en las clases `LibroDB` y `UsuarioDB`. Las tablas deben ser `libros` y `usuarios`).
        *   **Tabla `libros`:** `id` (INT, PK, AUTO_INCREMENT), `titulo` (VARCHAR), `autor` (VARCHAR), `fecha_publicacion` (INT), `imagen` (VARCHAR).
        *   **Tabla `usuarios`:** `id` (INT, PK, AUTO_INCREMENT), `email` (VARCHAR, UNIQUE), `password` (VARCHAR), `token_verificacion` (VARCHAR, NULLABLE), `verificado` (TINYINT, por defecto 0), `fecha_creacion` (DATETIME, por defecto CURRENT_TIMESTAMP), `ultimo_acceso` (DATETIME, NULLABLE), `bloqueado` (TINYINT, por defecto 0).

3.  **Configuración:**
    *   Renombra `config/config_ejemplo.php` a `config/config.php`.
    *   Abre `config/config.php` y actualiza las credenciales de la base de datos y la configuración del correo:
        ```php
        define('DB_HOST', 'tu_host_db');
        define('DB_USER', 'tu_usuario_db');
        define('DB_PASS', 'tu_contraseña_db');
        define('DB_NAME', 'tu_nombre_db');

        // Para el envío de correos (PHPMailer)
        define('MAIL_HOST', 'tu_host_smtp');
        define('MAIL_USER', 'tu_usuario_smtp');
        define('MAIL_PASS', 'tu_contraseña_smtp');
        ```

4.  **Configuración del Servidor Web (Apache):**
    *   Asegúrate de que tu servidor Apache esté configurado para usar archivos `.htaccess` para la reescritura de URL. El archivo `.htaccess` proporcionado maneja el enrutamiento a `api/index.php`.

## Documentación de la API

### URL Base

La URL base para la API es `http://localhost/ApiBiblioteca/api/libros` (asumiendo que estás ejecutando en XAMPP/Apache).

### Autenticación

Actualmente, los endpoints de la API de libros no requieren autenticación directa. La autenticación de usuarios se maneja por separado a través del panel de administración para tareas administrativas.

### Endpoints de Libros

Todas las operaciones relacionadas con libros se manejan a través del endpoint `/api/libros`.

#### Obtener Todos los Libros

Recupera una lista de todos los libros en la biblioteca.

-   **URL**: `/api/libros`
-   **Método**: `GET`
-   **Respuesta**: `200 OK`
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "titulo": "Cien años de soledad",
                "autor": "Gabriel García Márquez",
                "fecha_publicacion": 1967,
                "imagen": "cien_anios_de_soledad.jpg"
            },
            // ... más libros
        ],
        "count": 1
    }
    ```

#### Obtener Libro por ID

Recupera un solo libro por su ID.

-   **URL**: `/api/libros/{id}`
-   **Método**: `GET`
-   **Parámetros de URL**: `id` (entero, requerido) - El ID del libro.
-   **Respuesta**: `200 OK` si se encuentra, `404 Not Found` si no.
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "titulo": "Cien años de soledad",
            "autor": "Gabriel García Márquez",
            "fecha_publicacion": 1967,
            "imagen": "cien_anios_de_soledad.jpg"
        }
    }
    ```
    **Respuesta de Error (404 Not Found)**:
    ```json
    {
        "success": false,
        "error": "Libro no encontrado"
    }
    ```

#### Crear Libro

Crea un nuevo registro de libro. Soporta tanto cuerpo JSON como `multipart/form-data` para la carga de imágenes.

-   **URL**: `/api/libros`
-   **Método**: `POST`
-   **Cabeceras**:
    *   `Content-Type: application/json` (si no hay imagen)
    *   `Content-Type: multipart/form-data` (si se incluye imagen)
-   **Cuerpo de la Solicitud (JSON)**:
    ```json
    {
        "titulo": "Nuevo Libro",
        "autor": "Autor del Nuevo Libro",
        "fecha_publicacion": 2023
    }
    ```
-   **Cuerpo de la Solicitud (multipart/form-data)**:
    *   `datos` (cadena JSON): `{"titulo": "Nuevo Libro", "autor": "Autor del Nuevo Libro", "fecha_publicacion": 2023}`
    *   `imagen` (Archivo): El archivo de imagen para la portada del libro.
-   **Respuesta**: `201 Created` si es exitoso, `422 Unprocessable Entity` o `500 Internal Server Error` si falla.
    ```json
    {
        "success": true,
        "data": {
            "titulo": "Nuevo Libro",
            "autor": "Autor del Nuevo Libro",
            "fecha_publicacion": 2023,
            "imagen": "nuevo_libro.jpg",
            "id": 2
        },
        "message": "Libro creado con exito"
    }
    ```
    **Respuesta de Error (422 Unprocessable Entity - Datos Inválidos)**:
    ```json
    {
        "success": false,
        "error": "Datos de entrada inválidos. Se requiere título y autor. La fecha tiene formato (YYYY)"
    }
    ```
    **Respuesta de Error (422 Unprocessable Entity - Imagen Inválida)**:
    ```json
    {
        "success": false,
        "error": "Imagen inválida La imagen no puede superar 1MB"
    }
    ```
    **Respuesta de Error (500 Internal Server Error - Error al Guardar Imagen)**:
    ```json
    {
        "success": false,
        "error": "Error al guardar la imagen en el servidor"
    }
    ```

#### Actualizar Libro

Actualiza un registro de libro existente. Soporta tanto cuerpo JSON como `multipart/form-data` para la carga de imágenes.

-   **URL**: `/api/libros/{id}`
-   **Método**: `PUT` (o `POST` con `_method=PUT` en `multipart/form-data`)
-   **Parámetros de URL**: `id` (entero, requerido) - El ID del libro a actualizar.
-   **Cabeceras**:
    *   `Content-Type: application/json` (si no hay imagen)
    *   `Content-Type: multipart/form-data` (si se incluye imagen)
-   **Cuerpo de la Solicitud (JSON)**:
    ```json
    {
        "titulo": "Libro Actualizado",
        "autor": "Autor Actualizado",
        "fecha_publicacion": 2024
    }
    ```
-   **Cuerpo de la Solicitud (multipart/form-data)**:
    *   `_method` (cadena): `PUT` (requerido para la suplantación de método)
    *   `datos` (cadena JSON): `{"titulo": "Libro Actualizado", "autor": "Autor Actualizado", "fecha_publicacion": 2024}`
    *   `imagen` (Archivo, opcional): El nuevo archivo de imagen para la portada del libro.
-   **Respuesta**: `200 OK` si es exitoso, `404 Not Found`, `422 Unprocessable Entity` o `500 Internal Server Error` si falla.
    ```json
    {
        "success": true,
        "message": "Libro actualizado exitosamente",
        "data": {
            "titulo": "Libro Actualizado",
            "autor": "Autor Actualizado",
            "fecha_publicacion": 2024,
            "imagen": "libro_actualizado.jpg"
        }
    }
    ```

#### Eliminar Libro

Elimina un registro de libro por su ID.

-   **URL**: `/api/libros/{id}`
-   **Método**: `DELETE` (o `POST` con `_method=DELETE` en `multipart/form-data`)
-   **Parámetros de URL**: `id` (entero, requerido) - El ID del libro a eliminar.
-   **Respuesta**: `200 OK` si es exitoso, `404 Not Found` o `500 Internal Server Error` si falla.
    ```json
    {
        "success": true,
        "message": "Libro eliminado"
    }
    ```

### Endpoints de Usuario (Interacción con el Panel de Administración)

La gestión de usuarios (inicio de sesión, registro, recuperación de contraseña, verificación de cuenta) se maneja principalmente a través de la interfaz `admin/`, que interactúa con `controllers/usuarioController.php`. Estos no son endpoints de API RESTful directos de la misma manera que la API de libros, sino interacciones basadas en formularios.

-   **Inicio de Sesión**: `admin/login.php` (solicitud POST a `controllers/usuarioController.php`)
-   **Registro**: `admin/login.php` (solicitud POST a `controllers/usuarioController.php`)
-   **Recuperación de Contraseña**: `admin/login.php` (solicitud POST a `controllers/usuarioController.php`)
-   **Verificación de Cuenta**: `admin/verificar.php` (solicitud GET con parámetro `token`)
-   **Restablecimiento de Contraseña**: `admin/restablecer.php` (solicitud POST con `token` y `password`)

## Panel de Administración

El directorio `admin/` contiene la interfaz administrativa basada en web para gestionar usuarios y, potencialmente, otras configuraciones del sistema. Accede a ella a través de `http://localhost/ApiBiblioteca/admin/`.

## Contribución

¡Las contribuciones son bienvenidas! No dudes en enviar una solicitud de extracción (pull request).

## Licencia

Este proyecto es de código abierto y está disponible bajo la [Licencia MIT](LICENSE).
<?php
    // comprobar si el usuario está logueado y si no está logueado lo mandamos a login
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION['logueado']) || !$_SESSION['logueado']){
        header("Location: login.php");
    }
    
    $mensaje = '';
    if(isset($_SESSION['mensaje'])){
        $mensaje = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de control</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="container">
        <h1>📚 Panel de Control - Biblioteca</h1>
        
        <!-- Header con controles mejorado -->
        <div class="header-controls">
            <div class="user-info">
                <?php if(isset($_SESSION['usuario'])): ?>
                    <span>👋 Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']['nombre'] ?? $_SESSION['usuario']['correo'] ?? 'Usuario'); ?></strong></span>
                <?php else: ?>
                    <span>👋 Bienvenido al sistema</span>
                <?php endif; ?>
            </div>
            <div class="logout-container">
                <button id="cerrarSesion">🚪 Cerrar Sesión</button>
            </div>
        </div>

        <!-- Mensaje de estado -->
        <?php if($mensaje): ?>
            <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <!-- Panel de creación -->
        <div class="panelCrear">
            <button id="crear" class="btn-crear">➕ Crear nuevo libro</button>
        </div>

        <!-- Formulario (inicialmente oculto) -->
        <form method="POST" enctype="multipart/form-data">
            <h2>📚 Nuevo Libro</h2>
            
            <div class="form-group">
                <label for="titulo">📖 Título</label>
                <input type="text" id="titulo" name="titulo" required placeholder="Introduce el título del libro">
                <small class="error" id="error-titulo"></small>
            </div>

            <div class="form-group">
                <label for="autor">✍️ Autor</label>
                <input type="text" id="autor" name="autor" required placeholder="Nombre del autor">
                <small class="error" id="error-autor"></small>
            </div>

            <div class="form-group">
                <label for="genero">🎭 Género</label>
                <input type="text" id="genero" name="genero" placeholder="Ej: Ficción, Drama, Ciencia ficción...">
            </div>

            <div class="form-group">
                <label for="fecha_publicacion">📅 Fecha de publicación</label>
                <input type="number" id="fecha_publicacion" name="fecha_publicacion" min="1000" max="<?php echo date('Y') + 1; ?>" placeholder="<?php echo date('Y'); ?>">
                <small class="error" id="error-publicacion"></small>
            </div>

            <div class="form-group">
                <label for="imagen">🖼️ Imagen</label>
                <input type="file" id="imagen" name="imagen" accept="image/*">
                <small class="error" id="error-imagen"></small>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="disponible" name="disponible" checked>
                <label for="disponible">✅ Disponible</label>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="favorito" name="favorito">
                <label for="favorito">⭐ Favorito</label>
            </div>

            <div class="form-group">
                <label for="resumen">📝 Resumen</label>
                <textarea name="resumen" id="resumen" rows="6" placeholder="Escribe un breve resumen del libro..." maxlength="1000"></textarea>
                <small class="error" id="error-resumen"></small>
            </div>

            <button type="submit" id="btnGuardar">💾 Guardar libro</button>
        </form>

        <!-- Contenedor de tabla responsive -->
        <div class="tabla-container">
            <table class="tablaLibros" id="tablaLibros">
                <!-- La tabla se llena dinámicamente con JavaScript -->
            </table>
        </div>
    </div>

    <script src="js/funciones.js"></script>
    <script src="js/sesiones.js"></script>
</body>
</html>
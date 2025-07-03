<?php
/**
 * Para guardar los datos de una sesion en php se utiliza la variable superglobal
 * $_SESSION es un array asociativo
 * 
 * Para poder utilizar esta variables tenemos que iniciar sesion
 * session_start()
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Comprobar si el usuario ya está logueado
if(isset($_SESSION['logueado']) && $_SESSION['logueado']) {
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="post" action="../controllers/usuarioController.php">
            <input type="email" name="email" required placeholder="Correo electrónico">
            <input type="password" name="password" required placeholder="Contraseña">
            <input type="submit" name="login" value="Iniciar Sesión">
        </form>
        <div class="olvido-password">
            <a class="abrir-modal-recuperar">Recuperar Contraseña</a>
        </div>
        
        <div class="crear-cuenta">
            <p>¿No tienes una cuenta?</p>
            <a class="abrir-modal-registro">Crear cuenta nueva</a>
        </div>        
        <?php
        if(isset($_SESSION['mensaje'])){
            //si son incorrectos mostrar un mensaje de error
                echo "<div class='error'>" . $_SESSION['mensaje'] . "</div>";
                unset($_SESSION['mensaje']);
        }
        ?>
    </div>

    <div id="miModalRecuperar" class="modal">
        <div class="modal-contenido">
            <span class="cerrarRecuperar">&times;</span>
            <h2>Recuperar Contraseña</h2>
            <form method="POST" action="../controllers/usuarioController.php">
                <input type="email" name="email" required placeholder="Correo Electrónico">
                <input type="submit" name="recuperar" value="Recuperar Contraseña">
            </form>
        </div>
    </div>

    <div id="miModalRegistro" class="modal">
        <div class="modal-contenido">
            <span class="cerrarRegistro">&times;</span>
            <h2>Registro</h2>
            <form method="POST" action="../controllers/usuarioController.php">
                <input type="email" name="email" required placeholder="Correo Electrónico">
                <input type="password" name="password" required placeholder="Contraseña">
                <input type="submit" name="registro" value="Registrarse">
            </form>
        </div>
    </div>  
    <script src="js/login.js"></script>
</body>
</html>

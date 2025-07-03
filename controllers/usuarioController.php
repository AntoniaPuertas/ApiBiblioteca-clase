<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir las clases necesarias
require_once '../config/database.php';
require_once '../data/usuarioDB.php';

$database = new Database();
$usuariobd = new UsuarioDB($database);

function redirigirConMensaje($url, $success, $mensaje){
    //almacena el resultado en la sesion
    $_SESSION['success'] = $success;
    $_SESSION['mensaje'] = $mensaje;

    //realiza la redirección
    header("Location: $url");
    exit();
}



//Inicio de sesión
if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $resultado = $usuariobd->verificarCredenciales($email, $password);
    $_SESSION['logueado'] = $resultado['success'];

    if($resultado['success']){
        $_SESSION['usuario'] = $resultado['usuario'];
        $ruta = '../admin/index.php';
    }else{
        $ruta = '../admin/login.php';
    }
    redirigirConMensaje($ruta, $resultado['success'], $resultado['mensaje']);
}

//registro usuario
if(
    $_SERVER['REQUEST_METHOD'] == "POST" 
    && isset($_POST['registro'])
    && isset($_POST['email'])
    && isset($_POST['password'])
    ){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $resultado = $usuariobd->registrarUsuario($email, $password);

    redirigirConMensaje('../admin/login.php', $resultado['success'], $resultado['mensaje']);
}

//Recuperación de contraseña
if(
    $_SERVER['REQUEST_METHOD'] == "POST" 
    && isset($_POST['recuperar'])
    && isset($_POST['email'])
    ){

    $email = $_POST['email'];

    $resultado = $usuariobd->recuperarPassword($email);
    redirigirConMensaje('../admin/login.php', $resultado['success'], $resultado['mensaje']);
}
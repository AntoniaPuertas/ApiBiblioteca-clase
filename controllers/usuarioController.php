<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir las clases necesarias
require_once '../config/database.php';
require_once '../data/usuarioDB.php';

//comprobar que los datos llegan
if(!isset($_POST['email']) || !isset($_POST['password'])){ enviarALogin(); return;}

//comprobar que los datos sean correctos
$respuesta = comprobarDatos();
if($respuesta['error'])                                  { enviarALogin(); return; }

//comprobar si el usuario existe en la base
$resultado = consultarBase();

if(!$resultado['success']){
     enviarALogin(); 
     return;
}

//enviar al usuario al index
$_SESSION['mensaje'] = "Se ha logueado correctamente";
$_SESSION['nombre'] = $respuesta['usuario']['nombre'];
$_SESSION['logueado'] = true; 
header("Location: ../admin/index.php");
exit();

function enviarALogin(){
    header("Location: ../admin/login.php");
    exit();
}

function comprobarDatos(){
    $respuesta['error'] = false;
    //limpiar datos
    $email = $_POST['email'];
    $password = $_POST['password'];

    $email = trim($email);
    $email = strtolower($email);
     // Validar formato de email
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $respuesta['error'] = true;
        $respuesta['mensaje'] = "El formato del correo electrónico no es válido";
        return $respuesta;
    }

    if(strlen($password) < 4 || strlen($password) > 15){
        $_SESSION['mensaje'] = "La contraseña debe tener entre 4 y 15 caracteres";
        $respuesta['error'] = true;
    }
    return $respuesta;
}

function consultarBase(){
    try {
        // Crear conexión a la base de datos
        $database = new Database();
        $usuarioDB = new UsuarioDB($database);
        
        // Obtener los datos del formulario
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        // Verificar las credenciales
        $resultado = $usuarioDB->verificarCredenciales($email, $password);
        
        // Cerrar la conexión
        $database->close();
        $_SESSION['mensaje'] = $resultado['mensaje'];
        return $resultado['success'];
        
    } catch (Exception $e) {
        // Manejar errores de base de datos
        error_log("Error en consultarBase: " . $e->getMessage());
        $_SESSION['mensaje'] = 'Error interno del servidor. Inténtelo más tarde.';
        return false;
    }
}
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//recibe los datos de login y devuelve si son correctos o no

//comprobar que los datos llegan
// if(isset($_POST['email']) && isset($_POST['password'])){
//     $respuesta = comprobarDatos();
//     if($respuesta['error']){
//         //reenviamos a login.php
//         enviarALogin();
//     }else{
//         $resultado = consultarBase();
//         if($resultado){
//             //enviar al usuario al index
//             header("Location: ../admin/index.php");
//         }else{
//             enviarALogin();
//         }
//     }
// }else{
//     enviarALogin();
// }

//comprobar que los datos llegan
if(!isset($_POST['email']) || !isset($_POST['password'])){ enviarALogin(); return;}

//comprobar que los datos sean correctos
$respuesta = comprobarDatos();
if($respuesta['error'])                                  { enviarALogin(); return; }

//comprobar si el usuario existe en la base
$resultado = consultarBase();
if(!$resultado)                                          { enviarALogin(); return; }

//enviar al usuario al index
header("Location: ../admin/index.php");
$_SESSION['mensaje'] = "Se ha logueado correctamente";
//$_SESSION['nombre'] = $usuario;
$_SESSION['logueado'] = true; 

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
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if(strlen($password) < 4 || strlen($password) > 15){
        $_SESSION['mensaje'] = "La contraseña debe tener entre 4 y 15 caracteres";
        $respuesta['error'] = true;
    }
    return $respuesta;
}

function consultarBase(){
    //todo consultar si el usuario existe y la contraseña es correcta
    return true;
}
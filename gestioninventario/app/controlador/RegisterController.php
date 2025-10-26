<?php
session_start();
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../modelo/Usuario.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $nombre = $_POST["nombre"];
    $email = $_POST["email"];
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $telefono = $_POST["telefono"];
    $genero = $_POST["genero"];

    if($password !== $confirm_password){
        $error = "Las contraseñas no coinciden";
        header("Location: /gestioninventario/app/vista/registro.php?error=" . urlencode($error));
        exit();
    }

    $userModel = new Usuario($conn);

    if($userModel->registrarUsuario($nombre, $email, $usuario, $password, $telefono, $genero)){
        $success = "Usuario registrado correctamente";
        header("Location: /gestioninventario/app/vista/registro.php?success=" . urlencode($success));
        exit();
    } else {
        $error = "Error al registrar usuario. Verifica que el correo o usuario no estén duplicados";
        header("Location: /gestioninventario/app/vista/registro.php?error=" . urlencode($error));
        exit();
    }
}
?>
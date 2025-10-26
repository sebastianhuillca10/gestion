<?php
session_start();
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../modelo/Usuario.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];

    $userModel = new Usuario($conn);

    // Validar usuario y obtener info completa
    $datosUsuario = $userModel->validarUsuario($usuario, $password);

    if($datosUsuario){
        $_SESSION["usuario"] = $datosUsuario['usuario'];
        $_SESSION["tipo"] = $datosUsuario['tipo']; // Guardamos si es admin o usuario

        // Redirigir según el tipo
        if($datosUsuario['tipo'] == 'admin'){
            header("Location: ../vista/dashboard.php"); // Admin dashboard
        } else {
            header("Location: ../vista/usuario_dashboard.php"); // Usuario normal
        }
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
        header("Location: ../vista/login.php?error=" . urlencode($error));
        exit();
    }
}
?>
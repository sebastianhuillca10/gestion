<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/Usuario.php";

$usuario = new Usuario($conn);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id      = $_POST['id'] ?? null;
    $nombre  = $_POST['nombre'] ?? '';
    $email   = $_POST['email'] ?? '';
    $usuarioName = $_POST['usuario'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $genero  = $_POST['genero'] ?? '';
    $tipo    = $_POST['tipo'] ?? 'usuario';

    if($id && $nombre && $email && $usuarioName){
        $actualizado = $usuario->actualizarUsuario($id, $nombre, $email, $usuarioName, $telefono, $genero, $tipo);
        if($actualizado){
            header("Location: dashboard.php"); // Redirige al dashboard o lista de usuarios
            exit();
        } else {
            echo "Error al actualizar el usuario.";
        }
    } else {
        echo "Todos los campos obligatorios deben estar llenos.";
    }
}
?>
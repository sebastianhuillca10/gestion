<?php
session_start();

// Verificar sesión de admin
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

// Incluir conexión y modelo Usuario
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Usuario.php";

$usuarioObj = new Usuario($conn);

// Obtener ID del usuario a eliminar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id <= 0){
    die("ID de usuario inválido.");
}

// Eliminar usuario
if($usuarioObj->eliminarUsuario($id)){
    // Redirigir al dashboard
    header("Location: ../dashboard.php");
    exit();
} else {
    die("Error al eliminar el usuario.");
}
?>
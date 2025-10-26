<?php
session_start();

// Verificar sesión de admin
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

// Incluir conexión y modelo Producto
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Producto.php";

$productoObj = new Producto($conn);

// Obtener ID del producto a eliminar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id <= 0){
    die("ID de producto inválido.");
}

// Eliminar producto
if($productoObj->eliminarProducto($id)){
    // Redirigir al dashboard
    header("Location: ../dashboard.php");
    exit();
} else {
    die("Error al eliminar el producto.");
}
?>
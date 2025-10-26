<?php
session_start();

// Verificar sesión de admin
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

// Incluir conexión y modelo Proveedor
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Proveedor.php";

$proveedorObj = new Proveedor($conn);

// Obtener ID del proveedor a eliminar
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0){
    die("ID de proveedor inválido.");
}

// Intentar eliminar proveedor
if($proveedorObj->eliminarProveedor($id)){
    // Redirigir al dashboard con sección de proveedores
    header("Location: ../dashboard.php#proveedores");
    exit();
} else {
    die("Error al eliminar el proveedor.");
}
?>
<?php
session_start();

// Verificar sesión de admin
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

// Incluir conexión y modelo Venta
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Venta.php";

$ventaObj = new Venta($conn);

// Obtener ID de la venta a eliminar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id <= 0){
    die("ID de venta inválido.");
}

// Eliminar venta
if($ventaObj->eliminarVenta($id)){
    // Redirigir al dashboard con hash ventas
    header("Location: ../dashboard.php#ventas");
    exit();
} else {
    die("No se encontró la venta especificada o hubo un error al eliminar.");
}
?>
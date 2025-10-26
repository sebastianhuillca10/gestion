<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Venta.php";

$ventaModel = new Venta($conn);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_ids = $_POST['producto_id'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $precios = $_POST['precio_unitario'] ?? [];
    $tipo_comprobante = $_POST['tipo_comprobante'] ?? 'Boleta';

    $productos = [];

    // Recolectar productos válidos
    for($i=0; $i < count($producto_ids); $i++){
        $id = intval($producto_ids[$i]);
        $cantidad = intval($cantidades[$i]);
        $precio_unitario = floatval($precios[$i]);

        // Si el precio no está en el formulario, lo tomamos de la BD
        if($precio_unitario <= 0 && $id > 0){
            $stmt = $conn->prepare("SELECT precio_venta FROM productos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $fila = $stmt->get_result()->fetch_assoc();
            $precio_unitario = floatval($fila['precio_venta']);
        }

        if($id > 0 && $cantidad > 0){
            $productos[] = [
                'id' => $id,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio_unitario
            ];
        }
    }

    // Registrar venta y generar comprobante
    if(count($productos) > 0){
        $ultimo_id = $ventaModel->registrarVenta($productos, $tipo_comprobante);

        if($ultimo_id){
            $_SESSION['mensaje'] = "Venta(s) registrada(s) correctamente.";
            // ✅ Redirigir directamente al PDF del comprobante
            header("Location: generar_pdf.php?id=" . $ultimo_id);
            exit();
        } else {
            $_SESSION['error'] = "Error al registrar la venta.";
        }
    } else {
        $_SESSION['error'] = "No se han seleccionado productos válidos para la venta.";
    }

    header("Location: ../dashboard.php#ventas");
    exit();

} else {
    $_SESSION['error'] = "Acceso inválido al archivo.";
    header("Location: ../dashboard.php#ventas");
    exit();
}
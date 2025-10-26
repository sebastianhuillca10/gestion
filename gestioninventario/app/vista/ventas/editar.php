<?php
session_start();

// Verificar sesión y tipo de usuario
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Venta.php";
require_once __DIR__ . "/../../modelo/Producto.php";

$ventaObj = new Venta($conn);
$productoObj = new Producto($conn);

// Obtener ID de la venta a editar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0){
    die("ID de venta inválido.");
}

// Obtener datos de la venta
$ventaData = $ventaObj->obtenerVentaPorId($id);
if(!$ventaData){
    die("Venta no encontrada.");
}

// Lista de productos disponibles
$productos = $productoObj->obtenerProductosInventariados();

$producto_id = $ventaData['producto_id'];
$cantidad = $ventaData['cantidad'];
$precio_unitario = $ventaData['precio_unitario'];

$error = '';

// Procesar actualización
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nuevoProductoId = (int)$_POST['producto'];
    $nuevaCantidad = (int)$_POST['cantidad'];

    if($nuevaCantidad <= 0){
        $error = "La cantidad debe ser mayor que 0.";
    } else {
        // Recuperar datos del producto actual y nuevo
        $productoActual = $productoObj->obtenerProductoPorId($producto_id);
        $productoNuevo = $productoObj->obtenerProductoPorId($nuevoProductoId);

        if(!$productoNuevo){
            $error = "El producto seleccionado no existe.";
        } else {
            // Ajustar stock: devolver la cantidad anterior al producto antiguo
            $productoObj->actualizarStock($producto_id, $cantidad, 'sumar');

            // Verificar stock disponible en el nuevo producto
            if($nuevaCantidad > $productoNuevo['stock']){
                $error = "No hay suficiente stock en el producto seleccionado.";
                // Restaurar stock original del producto antiguo
                $productoObj->actualizarStock($producto_id, $cantidad, 'sumar');
            } else {
                // Actualizar stock del nuevo producto
                $productoObj->actualizarStock($nuevoProductoId, $nuevaCantidad, 'restar');

                // Calcular montos
                $precioUnitarioNuevo = $productoNuevo['precio_venta'];
                $subtotal = $nuevaCantidad * $precioUnitarioNuevo;
                $igv = round($subtotal * 0.18, 2);
                $total = $subtotal + $igv;

                // Actualizar venta
                if($ventaObj->actualizarVentaCompleta($id, $nuevoProductoId, $nuevaCantidad, $subtotal, $igv, $total, $precioUnitarioNuevo)){
                    header("Location: listar.php");
                    exit();
                } else {
                    $error = "Error al actualizar la venta.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Venta</title>
<link rel="stylesheet" href="../../public/css/estilos.css">
<style>
body { font-family: Arial, sans-serif; background:#f4f6f8; margin:0; padding:20px; display:flex; justify-content:center; align-items:center; height:100vh; }
.container { max-width: 500px; width:100%; background:#fff; padding:30px; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:20px; color:#1877f2; }
input, select { width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #ccc; box-sizing:border-box; font-size:14px;}
button { margin-top:20px; padding:10px; width:100%; font-size:16px; background:#28a745; color:#fff; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#218838; }
.error { color:red; text-align:center; margin-bottom:10px; }
a.back-link { display:block; text-align:center; margin-top:15px; text-decoration:none; color:#555; }
a.back-link:hover { text-decoration:underline; color:#1877f2; }
</style>
</head>
<body>
<div class="container">
<h2>Editar Venta</h2>

<?php if(!empty($error)): ?>
<p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Producto</label>
    <select name="producto" required>
        <?php foreach($productos as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $p['id'] == $producto_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nombre']) ?> — Stock: <?= $p['stock'] ?> — S/ <?= number_format($p['precio_venta'],2) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Cantidad</label>
    <input type="number" name="cantidad" value="<?= $cantidad ?>" min="1" required>

    <button type="submit">Actualizar Venta</button>
</form>

<a class="back-link" href="listar.php">← Volver al Listado de Ventas</a>
</div>
</body>
</html>
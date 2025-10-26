<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Producto.php";

$error = '';
$success = '';

$productoModel = new Producto($conn);

// Categorías disponibles
$categorias = [
    'Frutas','Verduras','Tubérculos','Granos y Legumbres',
    'Pescados y Mariscos','Carnes','Lácteos','Panadería',
    'Abarrotes','Bebidas','Productos de limpieza','Artículos de ferretería'
];

// Inventariado opciones
$inventariadoOpciones = ['Sí', 'No'];

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nombre = trim($_POST['nombre']);
    $categoria = $_POST['categoria'];
    $inventariado = $_POST['inventariado'];
    $precio_compra = $_POST['precio_compra'];
    $precio_venta = $_POST['precio_venta'];
    $stock = $_POST['stock'];

    if($nombre && $categoria && $inventariado && $precio_compra !== '' && $precio_venta !== '' && $stock !== ''){
        if($productoModel->agregarProducto($nombre, $categoria, $inventariado, $precio_compra, $precio_venta, $stock)){
            $success = "Producto agregado correctamente.";
        } else {
            $error = "Error al agregar producto.";
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar Producto - Admin</title>
<link rel="stylesheet" href="../../public/css/estilos.css">
<style>
body { font-family: Arial, sans-serif; background-color:#f4f6f8; display:flex; justify-content:center; align-items:center; height:100vh;}
.card {background:#fff;padding:30px 40px;border-radius:10px;box-shadow:0 10px 25px rgba(0,0,0,0.1); width:450px;}
h2 {text-align:center;margin-bottom:25px;color:#333;}
form input, form select {width:100%;padding:10px 12px;margin:8px 0;border-radius:6px;border:1px solid #ccc; font-size:14px; box-sizing:border-box;}
.input-precio {position:relative;margin:8px 0;}
.input-precio .simbolo {position:absolute;top:50%;left:10px;transform:translateY(-50%);font-weight:bold;color:#555;}
.input-precio input {padding-left:35px;}
form button {width:100%;padding:12px;background-color:#28a745;color:white;border:none;border-radius:6px;font-size:16px;cursor:pointer;margin-top:10px;}
form button:hover {background-color:#218838;}
.message {text-align:center;margin-bottom:10px;font-weight:bold;}
.error {color:#e74c3c;}
.success {color:#28a745;}
a.back-link {display:block;text-align:center;margin-top:15px;color:#555;text-decoration:none;}
a.back-link:hover {text-decoration:underline;color:#1877f2;}
</style>
</head>
<body>
<div class="card">
<h2>Agregar Nuevo Producto</h2>

<?php if($error != ''): ?>
    <div class="message error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if($success != ''): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST">
    <input type="text" name="nombre" placeholder="Nombre del producto" required>

    <select name="categoria" required>
        <option value="">Seleccione categoría</option>
        <?php foreach($categorias as $cat): ?>
            <option value="<?= $cat ?>"><?= $cat ?></option>
        <?php endforeach; ?>
    </select>

    <select name="inventariado" required>
        <?php foreach($inventariadoOpciones as $op): ?>
            <option value="<?= $op ?>"><?= $op ?></option>
        <?php endforeach; ?>
    </select>

    <div class="input-precio">
        <span class="simbolo">S/</span>
        <input type="number" step="0.01" name="precio_compra" placeholder="Precio de compra" required>
    </div>

    <div class="input-precio">
        <span class="simbolo">S/</span>
        <input type="number" step="0.01" name="precio_venta" placeholder="Precio de venta" required>
    </div>

    <input type="number" name="stock" placeholder="Stock" min="0" required>

    <button type="submit">Agregar Producto</button>
</form>

<a class="back-link" href="../dashboard.php#productos">← Volver al Dashboard</a>
</div>
</body>
</html>
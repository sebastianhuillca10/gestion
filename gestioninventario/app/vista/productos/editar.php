<?php
session_start();

// Verificar sesión y tipo de usuario
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

// Rutas según tu estructura
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Producto.php";

$productoObj = new Producto($conn);

// Obtener ID del producto a editar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0){
    die("ID de producto inválido.");
}

// Obtener datos del producto
$productoData = $productoObj->obtenerProductoPorId($id);
if(!$productoData){
    die("Producto no encontrado.");
}

// Inicializar variables
$nombre          = $productoData['nombre'] ?? '';
$categoria       = $productoData['categoria'] ?? '';
$inventariado    = $productoData['inventariado'] ?? '';
$precio_compra   = $productoData['precio_compra'] ?? '';
$precio_venta    = $productoData['precio_venta'] ?? '';
$stock           = $productoData['stock'] ?? '';

$error = '';

// Listar categorías para el select
$categoriasLista = $productoObj->listarCategorias();

// Procesar actualización
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nombre        = trim($_POST['nombre']);
    $categoria     = $_POST['categoria'] ?? '';
    $inventariado  = $_POST['inventariado'] ?? '';
    $precio_compra = $_POST['precio_compra'];
    $precio_venta  = $_POST['precio_venta'];
    $stock         = $_POST['stock'];

    // Validación básica
    if(empty($nombre) || empty($categoria) || empty($inventariado) || $precio_compra === '' || $precio_venta === '' || $stock === ''){
        $error = "Todos los campos son obligatorios.";
    } elseif(!is_numeric($precio_compra) || !is_numeric($precio_venta) || !is_numeric($stock)){
        $error = "Precio y stock deben ser valores numéricos.";
    } else {
        // Actualizar producto
        if($productoObj->actualizarProducto($id, $nombre, $categoria, $inventariado, $precio_compra, $precio_venta, $stock)){
            header("Location: ../dashboard.php"); // Redirige al dashboard
            exit();
        } else {
            $error = "Error al actualizar el producto. Verifica los datos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Producto</title>
<link rel="stylesheet" href="../../public/css/estilos.css">
<style>
body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
.container { max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.1);}
h2 { text-align: center; margin-bottom: 20px; color: #1877f2; }
form label { display: block; margin-top: 15px; font-weight: bold; }
form input, form select { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; }
button { margin-top: 20px; padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 16px; }
button:hover { background-color: #218838; }
button.cancel { background-color: #6c757d; margin-top:10px; }
button.cancel:hover { background-color: #5a6268; }
.error { color: red; text-align: center; margin-bottom: 10px; }
</style>
</head>
<body>
<div class="container">
    <h2>Editar Producto</h2>

    <?php if(!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

        <label>Categoría</label>
        <select name="categoria" required>
            <?php foreach($categoriasLista as $cat): ?>
            <option value="<?= htmlspecialchars($cat['nombre']) ?>" <?= $categoria === $cat['nombre'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Inventariado</label>
        <input type="text" name="inventariado" value="<?= htmlspecialchars($inventariado) ?>" required>

        <label>Precio Compra</label>
        <input type="number" step="0.01" name="precio_compra" value="<?= htmlspecialchars($precio_compra) ?>" required>

        <label>Precio Venta</label>
        <input type="number" step="0.01" name="precio_venta" value="<?= htmlspecialchars($precio_venta) ?>" required>

        <label>Stock</label>
        <input type="number" name="stock" value="<?= htmlspecialchars($stock) ?>" required>

        <button type="submit">Actualizar Producto</button>
        <button type="button" class="cancel" onclick="window.location.href='../dashboard.php'">Cancelar</button>
    </form>
</div>
</body>
</html>
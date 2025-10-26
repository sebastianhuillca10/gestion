<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Producto.php";

$productoModel = new Producto($conn);

// Parámetros de búsqueda y paginación
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$inicio = ($pagina_actual - 1) * 10; // 10 productos por página

// Contar productos con filtro
$totalProductosFiltro = $productoModel->contarProductos($busqueda);
$total_paginas = ceil($totalProductosFiltro / 10);

// Obtener productos paginados y ordenados alfabéticamente
$productosLista = $productoModel->obtenerProductosPaginados($busqueda, $inicio, 10, 'nombre ASC');

// Categorías disponibles
$categorias = $productoModel->listarCategorias();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Productos</title>
<link rel="stylesheet" href="../../public/css/estilos.css">
<style>
body { font-family: Arial, sans-serif; background:#f0f2f5; padding:20px; }
h2 { text-align:center; margin-bottom:20px; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; }
table, th, td { border:1px solid #ddd; }
th, td { padding:12px; text-align:center; }
th { background:#1877f2; color:white; }
a.edit, a.delete { text-decoration:none; padding:5px 10px; border-radius:4px; color:#fff; }
a.edit { background:#28a745; }
a.edit:hover { background:#218838; }
a.delete { background:#dc3545; }
a.delete:hover { background:#c82333; }
.btn-add { background:#28a745; color:white; padding:10px 15px; border:none; border-radius:6px; cursor:pointer; margin-bottom:15px; }
.btn-add:hover { background:#218838; }
.pagination { margin-top:20px; text-align:center; }
.pagination a { color:#1877f2; padding:8px 12px; text-decoration:none; border:1px solid #1877f2; margin:0 3px; border-radius:5px; transition:0.3s; }
.pagination a.active { background:#1877f2; color:white; }
.pagination a:hover { background:#155db2; color:white; }
.search-container { display:flex; justify-content:space-between; margin-bottom:10px; }
</style>
</head>
<body>

<h2>Gestión de Productos</h2>

<div class="search-container">
<form method="GET" action="">
    <input type="text" name="busqueda" placeholder="Buscar producto o categoría..." value="<?= htmlspecialchars($busqueda) ?>" style="padding:8px; width:250px;">
    <button type="submit" class="btn-add">Buscar</button>
    <?php if(!empty($busqueda)): ?>
    <a href="index.php" class="btn-add" style="background:#6c757d;">Limpiar</a>
    <?php endif; ?>
</form>

<button class="btn-add" onclick="window.location.href='agregar.php'">Agregar Producto</button>
</div>

<?php if(count($productosLista) == 0): ?>
<p>No hay productos registrados.</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Categoría</th>
<th>Inventariado</th>
<th>Precio Compra</th>
<th>Precio Venta</th>
<th>Stock</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php
$contador = ($pagina_actual - 1) * 10 + 1; // Contador visible según la página
foreach($productosLista as $p): ?>
<tr>
    <td><?= $contador ?></td>
    <td><?= htmlspecialchars($p['nombre']) ?></td>
    <td><?= htmlspecialchars($p['categoria']) ?></td>
    <td><?= htmlspecialchars($p['inventariado']) ?></td>
    <td>S/ <?= number_format($p['precio_compra'],2) ?></td>
    <td>S/ <?= number_format($p['precio_venta'],2) ?></td>
    <td><?= $p['stock'] ?></td>
    <td>
        <a class="edit" href="editar.php?id=<?= $p['id'] ?>">Editar</a>
        <a class="delete" href="eliminar.php?id=<?= $p['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este producto?')">Eliminar</a>
    </td>
</tr>
<?php $contador++; endforeach; ?>
</tbody>
</table>

<div class="pagination">
<?php for($i=1;$i<=$total_paginas;$i++): ?>
<a href="?pagina=<?= $i ?>&busqueda=<?= urlencode($busqueda) ?>" class="<?= $i==$pagina_actual?'active':'' ?>"><?= $i ?></a>
<?php endfor; ?>
</div>
<?php endif; ?>

</body>
</html>
<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Venta.php";
require_once __DIR__ . "/../../modelo/Producto.php";

$ventaModel = new Venta($conn);
$productoModel = new Producto($conn);

// Parámetros de búsqueda y paginación
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$resultados_por_pagina = 10;

// Obtener todas las ventas filtradas
$todasVentas = $ventaModel->obtenerVentas();
if (!empty($busqueda)) {
    $todasVentas = array_filter($todasVentas, function ($v) use ($busqueda) {
        return stripos($v['producto'], $busqueda) !== false;
    });
}

// Paginación
$totalVentas = count($todasVentas);
$total_paginas = max(1, ceil($totalVentas / $resultados_por_pagina));
$inicio = ($pagina_actual - 1) * $resultados_por_pagina;
$ventasLista = array_slice($todasVentas, $inicio, $resultados_por_pagina);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Ventas</title>
<link rel="stylesheet" href="../../public/css/estilos.css">
<style>
body {
    font-family: Arial, sans-serif;
    background: #f0f2f5;
    padding: 20px;
}
h2 {
    text-align: center;
    margin-bottom: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}
th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
}
th {
    background: #1877f2;
    color: white;
}
a.delete {
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 4px;
    color: #fff;
    background: #dc3545;
}
a.delete:hover {
    background: #c82333;
}
.btn-add {
    background: #28a745;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 15px;
}
.btn-add:hover {
    background: #218838;
}
.pagination {
    margin-top: 20px;
    text-align: center;
}
.pagination a {
    color: #1877f2;
    padding: 8px 12px;
    text-decoration: none;
    border: 1px solid #1877f2;
    margin: 0 3px;
    border-radius: 5px;
    transition: 0.3s;
}
.pagination a.active {
    background: #1877f2;
    color: white;
}
.pagination a:hover {
    background: #155db2;
    color: white;
}
.search-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}
tfoot td {
    font-weight: bold;
    background: #e9ecef;
}
.success-message {
    background: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    text-align: center;
}
</style>
</head>
<body>

<h2>Gestión de Ventas</h2>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'venta_eliminada'): ?>
    <div class="success-message">✅ Venta eliminada correctamente.</div>
<?php endif; ?>

<div class="search-container">
<form method="GET" action="">
    <input type="text" name="busqueda" placeholder="Buscar por producto..." value="<?= htmlspecialchars($busqueda) ?>" style="padding:8px; width:250px;">
    <button type="submit" class="btn-add">Buscar</button>
    <?php if (!empty($busqueda)): ?>
        <a href="index.php" class="btn-add" style="background:#6c757d;">Limpiar</a>
    <?php endif; ?>
</form>

<button class="btn-add" onclick="window.location.href='nueva.php'">Registrar Venta</button>
</div>

<?php if (count($ventasLista) == 0): ?>
    <p>No hay ventas registradas.</p>
<?php else: ?>
<table>
<thead>
<tr>
    <th>#</th>
    <th>Producto</th>
    <th>Cantidad</th>
    <th>Subtotal (S/)</th>
    <th>IGV (18%)</th>
    <th>Total (S/)</th>
    <th>Fecha</th>
    <th>Acción</th>
</tr>
</thead>
<tbody>
<?php
$contador = $inicio + 1;
$total_subtotal = $total_igv = $total_total = 0;

foreach ($ventasLista as $v):
    $total_subtotal += $v['subtotal'];
    $total_igv += $v['igv'];
    $total_total += $v['total'];
?>
<tr>
    <td><?= $contador ?></td>
    <td><?= htmlspecialchars($v['producto']) ?></td>
    <td><?= $v['cantidad'] ?></td>
    <td><?= number_format($v['subtotal'], 2) ?></td>
    <td><?= number_format($v['igv'], 2) ?></td>
    <td><strong><?= number_format($v['total'], 2) ?></strong></td>
    <td><?= $v['fecha'] ?></td>
    <td>
        <a class="delete" href="eliminar.php?id=<?= $v['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar esta venta?')">Eliminar</a>
    </td>
</tr>
<?php
$contador++;
endforeach;
?>
</tbody>
<tfoot>
<tr>
    <td colspan="3">Totales Página</td>
    <td><?= number_format($total_subtotal, 2) ?></td>
    <td><?= number_format($total_igv, 2) ?></td>
    <td colspan="3"><?= number_format($total_total, 2) ?></td>
</tr>
</tfoot>
</table>

<div class="pagination">
<?php for ($i = 1; $i <= $total_paginas; $i++): ?>
    <a href="?pagina=<?= $i ?>&busqueda=<?= urlencode($busqueda) ?>" class="<?= $i == $pagina_actual ? 'active' : '' ?>"><?= $i ?></a>
<?php endfor; ?>
</div>
<?php endif; ?>

</body>
</html>
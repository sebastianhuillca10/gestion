<?php
session_start();

// 🔒 Verificar sesión de admin
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

// 📦 Conexión y modelos
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Venta.php";
require_once __DIR__ . "/../../modelo/Producto.php";

$venta = new Venta($conn);
$producto = new Producto($conn);

/* ==================================================
   🔍 BUSCADOR Y PAGINACIÓN
================================================== */
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;
$inicio = ($pagina - 1) * $por_pagina;

$todasVentas = $venta->obtenerVentas();
if (!empty($busqueda)) {
    $todasVentas = array_filter($todasVentas, function ($v) use ($busqueda) {
        return stripos($v['producto'], $busqueda) !== false;
    });
}

$total_ventas = count($todasVentas);
$total_paginas = ceil($total_ventas / $por_pagina);
$ventasLista = array_slice($todasVentas, $inicio, $por_pagina);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Ventas</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 20px; }
        .container { width: 95%; margin: auto; }
        h2 { text-align: center; color: #333; }
        .search-box { margin: 20px 0; display: flex; justify-content: space-between; }
        .search-box input { padding: 8px; width: 250px; border-radius: 4px; border: 1px solid #ccc; }
        .search-box button, .btn-add { padding: 8px 12px; background: #1877f2; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-add { background: #28a745; }
        .btn-add:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #1877f2; color: white; }
        a.delete { background: #dc3545; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
        a.delete:hover { background: #c82333; }
        .pagination { margin: 20px 0; text-align: center; }
        .pagination a { padding: 8px 12px; margin: 0 3px; border: 1px solid #1877f2; color: #1877f2; text-decoration: none; border-radius: 4px; }
        .pagination a.active { background: #1877f2; color: white; }
        .pagination a:hover { background: #155db1; color: white; }
        tfoot td { background: #e9ecef; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>Listado de Ventas</h2>

    <div class="search-box">
        <form method="GET" action="">
            <input type="text" name="busqueda" placeholder="Buscar por producto..." value="<?= htmlspecialchars($busqueda) ?>">
            <button type="submit">Buscar</button>
            <?php if (!empty($busqueda)): ?>
                <a href="listar.php" class="btn-add" style="background:#6c757d;">Limpiar</a>
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
                    <td><?= $contador++ ?></td>
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
                <?php endforeach; ?>
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

        <!-- PAGINACIÓN -->
        <div class="pagination">
            <?php if ($pagina > 1): ?>
                <a href="?pagina=<?= $pagina - 1 ?>&busqueda=<?= urlencode($busqueda) ?>">&laquo; Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?= $i ?>&busqueda=<?= urlencode($busqueda) ?>" class="<?= $i == $pagina ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($pagina < $total_paginas): ?>
                <a href="?pagina=<?= $pagina + 1 ?>&busqueda=<?= urlencode($busqueda) ?>">Siguiente &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
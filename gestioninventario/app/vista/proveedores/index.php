<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../modelo/Proveedor.php";

$proveedorModel = new Proveedor($conn);

// Parámetros de búsqueda y paginación
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$pagina_actual = isset($_GET['pagina']) ? max(1,intval($_GET['pagina'])) : 1;
$por_pagina = 5;
$inicio = ($pagina_actual - 1) * $por_pagina;

$totalProveedores = $proveedorModel->contarProveedores($busqueda);
$total_paginas = ceil($totalProveedores / $por_pagina);

$proveedoresLista = $proveedorModel->obtenerProveedoresPaginados($busqueda, $inicio, $por_pagina);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Proveedores</title>
<link rel="stylesheet" href="../../public/css/estilos.css">
<style>
body { font-family: Arial, sans-serif; background: #f5f6f8; margin: 20px; }
h2 { text-align: center; color: #333; }
.container { width: 95%; margin: 0 auto; }
.search-box { margin: 15px 0; text-align: right; }
.search-box input { padding: 8px; width: 250px; border: 1px solid #ccc; border-radius: 4px; }
.search-box button { padding: 8px 12px; background: #1877f2; color: white; border: none; border-radius: 4px; cursor: pointer; }
.search-box button:hover { background: #155db1; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.1); border-radius: 6px; overflow: hidden; }
th, td { padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
th { background-color: #1877f2; color: #fff; }
a.edit, a.delete { padding: 5px 10px; border-radius: 4px; color: #fff; text-decoration: none; display: inline-block; margin: 2px 0; }
a.edit { background-color: #28a745; }
a.edit:hover { background-color: #218838; }
a.delete { background-color: #dc3545; }
a.delete:hover { background-color: #c82333; }
button.add-proveedor { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; margin-top: 10px; }
button.add-proveedor:hover { background-color: #0069d9; }
.pagination { margin: 20px 0; text-align: center; }
.pagination a { padding: 8px 12px; margin: 0 4px; border: 1px solid #1877f2; color: #1877f2; text-decoration: none; border-radius: 4px; }
.pagination a.active { background-color: #1877f2; color: white; }
.pagination a:hover { background-color: #155db1; color: white; }
</style>
<script>
// Confirmación eliminar proveedor dentro del iframe
function confirmarEliminar(url) {
    if(confirm('¿Seguro que deseas eliminar este proveedor?')) {
        window.location.href = url; // redirige dentro del iframe
    }
    return false;
}
</script>
</head>
<body>
<div class="container">
<h2>Gestión de Proveedores</h2>

<div class="search-box">
<form method="GET" action="">
    <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar empresa o RUC...">
    <button type="submit">Buscar</button>
</form>
</div>

<button class="add-proveedor" onclick="window.location.href='agregar.php'">Agregar Proveedor</button>

<?php if(count($proveedoresLista) == 0): ?>
    <p>No hay proveedores registrados.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Empresa</th>
                <th>RUC</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php $num = $inicio + 1; ?>
            <?php foreach($proveedoresLista as $p): ?>
                <tr>
                    <td><?= $num++ ?></td>
                    <td><?= htmlspecialchars($p['empresa']) ?></td>
                    <td><?= htmlspecialchars($p['ruc']) ?></td>
                    <td><?= htmlspecialchars($p['telefono']) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= htmlspecialchars($p['direccion']) ?></td>
                    <td>
                        <a class="edit" href="editar.php?id=<?= $p['id'] ?>" target="_self">Editar</a>
                        <a class="delete" href="#" onclick="return confirmarEliminar('eliminar.php?id=<?= $p['id'] ?>');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Paginación -->
    <div class="pagination">
        <?php if($pagina_actual > 1): ?>
            <a href="?pagina=<?= $pagina_actual-1 ?>&busqueda=<?= urlencode($busqueda) ?>">&laquo; Anterior</a>
        <?php endif; ?>

        <?php for($i=1;$i<=$total_paginas;$i++): ?>
            <a href="?pagina=<?= $i ?>&busqueda=<?= urlencode($busqueda) ?>" class="<?= $i==$pagina_actual?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if($pagina_actual < $total_paginas): ?>
            <a href="?pagina=<?= $pagina_actual+1 ?>&busqueda=<?= urlencode($busqueda) ?>">Siguiente &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>
</body>
</html>
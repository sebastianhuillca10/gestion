<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../modelo/Usuario.php";
require_once __DIR__ . "/../modelo/Producto.php";
require_once __DIR__ . "/../modelo/Proveedor.php";
require_once __DIR__ . "/../modelo/Venta.php";

$usuarioModel = new Usuario($conn);
$productoModel = new Producto($conn);
$proveedorModel = new Proveedor($conn);
$ventaModel = new Venta($conn);

// Contadores
$totalUsuarios = $usuarioModel->contarUsuarios();
$totalProductos = $productoModel->contarProductos();
$totalProveedores = $proveedorModel->contarProveedores();
$totalVentas = $ventaModel->contarVentas();
// ================== DATOS PARA EL GRÁFICO ==================
$sqlVentas = "
    SELECT p.nombre AS producto, SUM(v.total) AS total_ventas, SUM(v.cantidad) AS total_cantidad
    FROM ventas v
    INNER JOIN productos p ON v.producto_id = p.id
    GROUP BY v.producto_id
";
$resultVentas = $conn->query($sqlVentas);

$productos = [];
$ventasTotales = [];
$productoMasVendido = ["nombre" => "Ninguno", "cantidad" => 0];

if ($resultVentas && $resultVentas->num_rows > 0) {
    while ($row = $resultVentas->fetch_assoc()) {
        $productos[] = $row['producto'];
        $ventasTotales[] = $row['total_ventas'];
        if ($row['total_cantidad'] > $productoMasVendido['cantidad']) {
            $productoMasVendido = [
                "nombre" => $row['producto'],
                "cantidad" => $row['total_cantidad']
            ];
        }
    }
}

// Parámetros usuarios
$busquedaUsuarios = isset($_GET['busqueda_usuarios']) ? trim($_GET['busqueda_usuarios']) : '';
$pagina_actual_usuarios = isset($_GET['pagina_usuarios']) ? max(1,intval($_GET['pagina_usuarios'])) : 1;
$inicio_usuarios = ($pagina_actual_usuarios - 1) * 5;
$totalUsuariosFiltro = $usuarioModel->contarUsuarios($busquedaUsuarios);
$total_paginas_usuarios = ceil($totalUsuariosFiltro / 5);
$usuariosLista = $usuarioModel->obtenerUsuariosPaginados($busquedaUsuarios, $inicio_usuarios, 5);

// Parámetros proveedores
$busquedaProveedores = isset($_GET['busqueda_proveedores']) ? trim($_GET['busqueda_proveedores']) : '';
$pagina_actual_proveedores = isset($_GET['pagina_proveedores']) ? max(1,intval($_GET['pagina_proveedores'])) : 1;
$inicio_proveedores = ($pagina_actual_proveedores - 1) * 5;
$totalProveedoresFiltro = $proveedorModel->contarProveedores($busquedaProveedores);
$total_paginas_proveedores = ceil($totalProveedoresFiltro / 5);
$proveedoresLista = $proveedorModel->obtenerProveedoresPaginados($busquedaProveedores, $inicio_proveedores, 5);

// Parámetros productos
$busquedaProductos = isset($_GET['busqueda_productos']) ? trim($_GET['busqueda_productos']) : '';
$pagina_actual_productos = isset($_GET['pagina_productos']) ? max(1,intval($_GET['pagina_productos'])) : 1;
$inicio_productos = ($pagina_actual_productos - 1) * 15;
$totalProductosFiltro = $productoModel->contarProductos($busquedaProductos);
$total_paginas_productos = ceil($totalProductosFiltro / 15);
$productosLista = $productoModel->obtenerProductosPaginados($busquedaProductos, $inicio_productos, 15);

// Parámetros ventas
$busquedaVentas = isset($_GET['busqueda_ventas']) ? trim($_GET['busqueda_ventas']) : '';
$pagina_actual_ventas = isset($_GET['pagina_ventas']) ? max(1,intval($_GET['pagina_ventas'])) : 1;
$resultados_por_pagina_ventas = 10;
$todasVentas = $ventaModel->obtenerVentas();
if (!empty($busquedaVentas)) {
    $todasVentas = array_filter($todasVentas, function ($v) use ($busquedaVentas) {
        return stripos($v['producto'], $busquedaVentas) !== false;
    });
}
$totalVentasFiltro = count($todasVentas);
$total_paginas_ventas = max(1, ceil($totalVentasFiltro / $resultados_por_pagina_ventas));
$inicio_ventas = ($pagina_actual_ventas - 1) * $resultados_por_pagina_ventas;
$ventasLista = array_slice($todasVentas, $inicio_ventas, $resultados_por_pagina_ventas, true);

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de Administración - Gestión de Inventario</title>
<link rel="stylesheet" href="../public/css/estilos.css">
<style>
body { font-family: Arial, sans-serif; margin:0; background:#f0f2f5; }
.sidebar { width:230px; height:100vh; background:#1877f2; color:#fff; position:fixed; padding-top:20px; display:flex; flex-direction:column; }
.sidebar h2 { text-align:center; margin-bottom:30px; }
.sidebar a { text-decoration:none; color:#fff; padding:12px 20px; margin:5px 0; display:block; border-radius:6px; transition:0.3s; }
.sidebar a:hover { background:#155db2; }
.main-content { margin-left:250px; padding:20px; }
.header { display:flex; justify-content:space-between; align-items:center; }
.cards { display:flex; gap:20px; margin-top:20px; flex-wrap:wrap; }
.card { flex:1; min-width:200px; background:#fff; padding:20px; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.1); text-align:center; }
.section { display:none; margin-top:20px; }
.section.active { display:block; }
.logout-btn { color:#E74C3C; font-weight:bold; text-decoration:none; }
.logout-btn:hover { text-decoration:underline; }
table { width:100%; border-collapse:collapse; margin-top:20px; background:#fff; border-radius:8px; overflow:hidden; }
table, th, td { border:1px solid #ddd; }
th, td { padding:12px; text-align:center; }
th { background:#1877f2; color:white; }
a.edit, a.delete { text-decoration:none; padding:5px 10px; border-radius:4px; color:#fff; }
a.edit { background:#28a745; }
a.edit:hover { background:#218838; }
a.delete { background:#dc3545; }
a.delete:hover { background:#c82333; }
.btn-primary, .btn-clear { background:#007bff; color:white; padding:10px 15px; border:none; border-radius:6px; cursor:pointer; }
.btn-primary:hover { background:#0069d9; }
.btn-clear { background:#6c757d; }
.btn-clear:hover { background:#5a6268; }
.btn-add-user, .btn-add { background:#28a745; border:none; padding:10px 15px; border-radius:6px; color:white; cursor:pointer; transition:0.3s; }
.btn-add-user:hover, .btn-add:hover { background:#218838; }
.search-container { display:flex; align-items:center; justify-content:space-between; margin-top:10px; flex-wrap:wrap; gap:10px; }
.pagination { margin-top:20px; text-align:center; }
.pagination a { color:#1877f2; padding:8px 12px; text-decoration:none; border:1px solid #1877f2; margin:0 3px; border-radius:5px; transition:0.3s; }
.pagination a.active { background:#1877f2; color:white; }
.pagination a:hover { background:#155db2; color:white; }

/* Modal eliminar */
#modalEliminar { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000; }
#modalEliminar div { background:white; padding:20px; border-radius:8px; width:320px; text-align:center; }
#modalEliminar button { padding:8px 15px; margin:5px; border:none; border-radius:6px; cursor:pointer; }
#modalEliminar .confirm { background:#dc3545; color:white; }
#modalEliminar .cancel { background:#6c757d; color:white; }

/* Formulario venta múltiple */
#formVentaMultiple select, #formVentaMultiple input { padding:5px; width:100%; box-sizing:border-box; }
#formVentaMultiple table { width:100%; margin-top:10px; }
#formVentaMultiple table th, #formVentaMultiple table td { padding:5px; }
#formVentaMultiple button { margin-top:5px; }
</style>
</head>
<body>
<div class="sidebar">
<h2>Admin Panel</h2>
<a href="#" onclick="mostrarSeccion('inicio')">🏠 Inicio</a>
<a href="#" onclick="mostrarSeccion('usuarios')">👤 Usuarios</a>
<a href="#" onclick="mostrarSeccion('productos')">📦 Productos</a>
<a href="#" onclick="mostrarSeccion('proveedores')">🚚 Proveedores</a>
<a href="#" onclick="mostrarSeccion('ventas')">💰 Ventas</a>
<a class="logout-btn" href="logout.php">🚪 Cerrar Sesión</a>
</div>

<div class="main-content">

<!-- ================== INICIO ================== -->
<div id="inicio" class="section active">
  <div class="header">
    <h1>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?>!</h1>
  </div>

  <div class="cards">
    <div class="card"><h3>Usuarios</h3><p><?= $totalUsuarios ?></p></div>
    <div class="card"><h3>Productos</h3><p><?= $totalProductos ?></p></div>
    <div class="card"><h3>Proveedores</h3><p><?= $totalProveedores ?></p></div>
    <div class="card"><h3>Ventas</h3><p><?= $totalVentas ?></p></div>
  </div>

  <!-- ================== GRÁFICO DE VENTAS ================== -->
  <div style="display:flex; flex-wrap:wrap; justify-content:center; align-items:stretch; gap:30px; margin-top:40px;">
    <!-- Gráfico circular -->
    <div style="flex:1 1 400px; background:#fff; padding:20px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); max-width:500px;">
      <h3 style="text-align:center;">Distribución de Ventas por Producto</h3>
      <canvas id="graficoVentas" style="width:100%; height:350px;"></canvas>
    </div>

    <!-- Producto más vendido -->
    <div style="flex:1 1 250px; background:#fff; padding:20px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center; display:flex; flex-direction:column; justify-content:center;">
      <h3>🏆 Producto Más Vendido</h3>
      <p style="font-size:1.2em; font-weight:bold; color:#1877f2; margin-top:10px;">
        <?= htmlspecialchars($productoMasVendido['nombre']) ?>
      </p>
      <p style="margin-top:5px;">Cantidad vendida: <strong><?= $productoMasVendido['cantidad'] ?></strong></p>
    </div>
  </div>
</div>
<!-- ================== FIN INICIO ================== -->
 <!-- Cargar Chart.js y dibujar el gráfico -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const ctx = document.getElementById('graficoVentas');
  if (!ctx) return; // No hace nada si no existe el canvas

  const data = {
    labels: <?= json_encode($productos) ?>,
    datasets: [{
      label: 'Ventas por producto',
      data: <?= json_encode($ventasTotales) ?>,
      backgroundColor: [
        '#1877f2','#28a745','#ffc107','#dc3545','#6f42c1',
        '#17a2b8','#fd7e14','#20c997','#6610f2','#e83e8c'
      ],
      borderWidth: 1
    }]
  };

  new Chart(ctx, {
    type: 'doughnut',
    data: data,
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
        title: {
          display: true,
          text: 'Ventas totales por producto'
        }
      }
    }
  });
});
</script>
</body>
</html>


<!-- Usuarios -->
<div id="usuarios" class="section">
<h2>Gestión de Usuarios</h2>
<div class="search-container">
<form method="GET" action="">
<input type="hidden" name="pagina_usuarios" value="1">
<input type="text" name="busqueda_usuarios" placeholder="Buscar usuario o nombre..." value="<?= htmlspecialchars($busquedaUsuarios) ?>" style="padding:8px; width:250px;">
<button type="submit" class="btn-primary">Buscar</button>
<?php if(!empty($busquedaUsuarios)): ?>
<a href="dashboard.php#usuarios" class="btn-clear">Limpiar</a>
<?php endif; ?>
</form>
<button class="btn-add-user" onclick="window.location.href='usuarios/agregar.php'">Agregar Usuario</button>
</div>

<?php if(count($usuariosLista) == 0): ?>
<p>No hay usuarios registrados.</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>ID</th><th>Nombre</th><th>Email</th><th>Usuario</th><th>Teléfono</th><th>Género</th><th>Tipo</th><th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($usuariosLista as $u): ?>
<tr>
<td><?= $u['id'] ?></td>
<td><?= htmlspecialchars($u['nombre']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= htmlspecialchars($u['usuario']) ?></td>
<td><?= htmlspecialchars($u['telefono']) ?></td>
<td><?= htmlspecialchars($u['genero']) ?></td>
<td><?= htmlspecialchars($u['tipo']) ?></td>
<td>
<a class="edit" href="usuarios/editar.php?id=<?= $u['id'] ?>&volver=dashboard.php#usuarios">Editar</a>
<a class="delete" href="#" onclick="abrirModal('usuarios', <?= $u['id'] ?>)">Eliminar</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="pagination">
<?php for($i=1;$i<=$total_paginas_usuarios;$i++): ?>
<a href="?pagina_usuarios=<?= $i ?>&busqueda_usuarios=<?= urlencode($busquedaUsuarios) ?>#usuarios" class="<?= $i==$pagina_actual_usuarios?'active':'' ?>"><?= $i ?></a>
<?php endfor; ?>
</div>
<?php endif; ?>
</div>

<!-- Productos -->
<div id="productos" class="section">
<h2>Gestión de Productos</h2>
<div class="search-container">
<form method="GET" action="">
<input type="hidden" name="pagina_productos" value="1">
<input type="text" name="busqueda_productos" placeholder="Buscar producto o categoría..." 
       value="<?= htmlspecialchars($busquedaProductos ?? '') ?>" style="padding:8px; width:250px;">
<button type="submit" class="btn-primary">Buscar</button>
<?php if(!empty($busquedaProductos)): ?>
<a href="dashboard.php#productos" class="btn-clear">Limpiar</a>
<?php endif; ?>
</form>
<button class="btn-add" onclick="window.location.href='productos/agregar.php'">Agregar Producto</button>
</div>

<?php if(count($productosLista) == 0): ?>
<p>No hay productos registrados.</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>ID</th><th>Nombre</th><th>Categoría</th><th>Inventariado</th><th>Precio Compra</th><th>Precio Venta</th><th>Stock</th><th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($productosLista as $prod): ?>
<tr>
<td><?= $prod['id'] ?></td>
<td><?= htmlspecialchars($prod['nombre']) ?></td>
<td><?= htmlspecialchars($prod['categoria']) ?></td>
<td><?= htmlspecialchars($prod['inventariado']) ?></td>
<td>S/ <?= number_format($prod['precio_compra'], 2) ?></td>
<td>S/ <?= number_format($prod['precio_venta'], 2) ?></td>
<td><?= $prod['stock'] ?></td>
<td>
<a class="edit" href="productos/editar.php?id=<?= $prod['id'] ?>&volver=dashboard.php#productos">Editar</a>
<a class="delete" href="#" onclick="abrirModal('productos', <?= $prod['id'] ?>)">Eliminar</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="pagination">
<?php for($i=1;$i<=$total_paginas_productos;$i++): ?>
<a href="?pagina_productos=<?= $i ?>&busqueda_productos=<?= urlencode($busquedaProductos ?? '') ?>#productos" class="<?= $i==$pagina_actual_productos?'active':'' ?>"><?= $i ?></a>
<?php endfor; ?>
</div>
<?php endif; ?>
</div>

<!-- Proveedores -->
<div id="proveedores" class="section">
<h2>Gestión de Proveedores</h2>
<div class="search-container">
<form method="GET" action="">
<input type="hidden" name="pagina_proveedores" value="1">
<input type="text" name="busqueda_proveedores" placeholder="Buscar proveedor o RUC..." value="<?= htmlspecialchars($busquedaProveedores) ?>" style="padding:8px; width:250px;">
<button type="submit" class="btn-primary">Buscar</button>
<?php if(!empty($busquedaProveedores)): ?>
<a href="dashboard.php#proveedores" class="btn-clear">Limpiar</a>
<?php endif; ?>
</form>
<button class="btn-add" onclick="window.location.href='proveedores/agregar.php'">Agregar Proveedor</button>
</div>

<?php if(count($proveedoresLista)==0): ?>
<p>No hay proveedores registrados.</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>ID</th><th>Empresa</th><th>RUC</th><th>Teléfono</th><th>Email</th><th>Dirección</th><th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($proveedoresLista as $p): ?>
<tr>
<td><?= $p['id'] ?></td>
<td><?= htmlspecialchars($p['empresa']) ?></td>
<td><?= htmlspecialchars($p['ruc']) ?></td>
<td><?= htmlspecialchars($p['telefono']) ?></td>
<td><?= htmlspecialchars($p['email']) ?></td>
<td><?= htmlspecialchars($p['direccion']) ?></td>
<td>
<a class="edit" href="proveedores/editar.php?id=<?= $p['id'] ?>&volver=dashboard.php#proveedores">Editar</a>
<a class="delete" href="#" onclick="abrirModal('proveedores', <?= $p['id'] ?>)">Eliminar</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="pagination">
<?php for($i=1;$i<=$total_paginas_proveedores;$i++): ?>
<a href="?pagina_proveedores=<?= $i ?>&busqueda_proveedores=<?= urlencode($busquedaProveedores) ?>#proveedores" class="<?= $i==$pagina_actual_proveedores?'active':'' ?>"><?= $i ?></a>
<?php endfor; ?>
</div>
<?php endif; ?>
</div>

<!-- Ventas -->
<div id="ventas" class="section">
<h2>Gestión de Ventas</h2>
<div class="search-container">
<form method="GET" action="">
<input type="hidden" name="pagina_ventas" value="1">
<input type="text" name="busqueda_ventas" placeholder="Buscar producto..." value="<?= htmlspecialchars($busquedaVentas) ?>" style="padding:8px; width:250px;">
<button type="submit" class="btn-primary">Buscar</button>
<?php if(!empty($busquedaVentas)): ?>
<a href="dashboard.php#ventas" class="btn-clear">Limpiar</a>
<?php endif; ?>
</form>
<button class="btn-add" onclick="mostrarFormularioVenta()">Registrar Venta Múltiple</button>
</div>

<!-- Formulario venta múltiple -->
<div id="formVentaMultiple" style="display:none; margin-top:20px; background:#fff; padding:20px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
<h3>Nueva Venta Múltiple</h3>
<form id="ventaForm" method="POST" action="ventas/guardar_multiples.php">
<table id="tablaVenta">
<thead>
<tr>
<th>Producto</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th><th>IGV</th><th>Total</th><th>Acción</th>
</tr>
</thead>
<tbody>
<tr>
<td>
<select name="producto_id[]" class="productoSelect" onchange="actualizarPrecio(this)">
<option value="">Selecciona un producto</option>
<?php foreach($productosLista as $prod): ?>
<option value="<?= $prod['id'] ?>" data-precio="<?= $prod['precio_venta'] ?>"><?= htmlspecialchars($prod['nombre']) ?> (S/ <?= number_format($prod['precio_venta'],2) ?>)</option>
<?php endforeach; ?>
</select>
</td>
<td><input type="number" name="cantidad[]" value="1" min="1" oninput="actualizarSubtotal(this)"></td>
<td class="precioUnitario">0.00
<input type="hidden" name="precio_unitario[]" class="inputPrecioUnitario">
</td>
<td class="subtotal">0.00
<input type="hidden" name="subtotal[]" class="inputSubtotal">
</td>
<td class="igv">0.00
<input type="hidden" name="igv[]" class="inputIGV">
</td>
<td class="total">0.00
<input type="hidden" name="total[]" class="inputTotal">
</td>
<td><button type="button" onclick="eliminarFila(this)">Eliminar</button></td>
</tr>
</tbody>
</table>
<button type="button" onclick="agregarFila()">Agregar Producto</button>
<hr>
<p>Subtotal Total: S/ <span id="totalSubtotal">0.00</span></p>
<p>IGV Total (18%): S/ <span id="totalIGV">0.00</span></p>
<p>Total Venta: S/ <span id="totalVenta">0.00</span></p>
<br>
<label>Tipo Comprobante:
<select name="tipo_comprobante" required>
<option value="Boleta">Boleta</option>
<option value="Factura">Factura</option>
</select>
</label>
<label>N° Comprobante: <input type="text" name="numero_comprobante" required></label>
<br><br>
<button type="submit" class="btn-primary">Registrar Venta</button>
<button type="button" class="btn-clear" onclick="cerrarFormularioVenta()">Cancelar</button>
</form>
</div>

<?php if(count($ventasLista) == 0): ?>
<p>No hay ventas registradas.</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>ID</th><th>Producto</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th><th>IGV</th><th>Total</th><th>Comprobante</th><th>N° Comprobante</th><th>Fecha</th><th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($ventasLista as $v): ?>
<tr>
<td><?= $v['id'] ?? '' ?></td>
<td><?= isset($v['producto']) ? htmlspecialchars($v['producto']) : '' ?></td>
<td><?= isset($v['cantidad']) ? $v['cantidad'] : 0 ?></td>
<td>S/ <?= isset($v['precio_unitario']) ? number_format($v['precio_unitario'],2) : '0.00' ?></td>
<td>S/ <?= isset($v['subtotal']) ? number_format($v['subtotal'],2) : '0.00' ?></td>
<td>S/ <?= isset($v['igv']) ? number_format($v['igv'],2) : '0.00' ?></td>
<td>S/ <?= isset($v['total']) ? number_format($v['total'],2) : '0.00' ?></td>
<td><?= isset($v['tipo_comprobante']) ? htmlspecialchars($v['tipo_comprobante']) : '' ?></td>
<td><?= isset($v['numero_comprobante']) ? htmlspecialchars($v['numero_comprobante']) : '' ?></td>
<td><?= isset($v['fecha']) ? $v['fecha'] : '' ?></td>
<td>
<a class="delete" href="#" onclick="abrirModal('ventas', <?= $v['id'] ?? 0 ?>)">Eliminar</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="pagination">
<?php for($i=1; $i<=$total_paginas_ventas; $i++): ?>
<a href="?pagina_ventas=<?= $i ?>&busqueda_ventas=<?= urlencode($busquedaVentas) ?>#ventas" class="<?= $i==$pagina_actual_ventas ? 'active' : '' ?>"><?= $i ?></a>
<?php endfor; ?>
</div>
<?php endif; ?>
</div>

<!-- Modal eliminar -->
<div id="modalEliminar">
<div>
<p id="modalMensaje">¿Seguro que deseas eliminar este registro?</p>
<button class="confirm" onclick="confirmarEliminar()">Sí, eliminar</button>
<button class="cancel" onclick="cerrarModal()">Cancelar</button>
</div>
</div>

<script>
function mostrarSeccion(id){
document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
document.getElementById(id).classList.add('active');
history.replaceState(null,null,'#'+id);
}
const hash = window.location.hash.substring(1);
if(hash) mostrarSeccion(hash);

// Modal eliminar
let tipoEliminar=null;
let idAEliminar=null;
function abrirModal(tipo,id){
tipoEliminar=tipo;
idAEliminar=id;
document.getElementById('modalMensaje').innerText = `¿Seguro que deseas eliminar este ${tipo==='usuarios'?'usuario':(tipo==='productos'?'producto':(tipo==='proveedores'?'proveedor':'venta'))}?`;
document.getElementById('modalEliminar').style.display='flex';
}
function cerrarModal(){ tipoEliminar=null; idAEliminar=null; document.getElementById('modalEliminar').style.display='none'; }
function confirmarEliminar(){ if(tipoEliminar && idAEliminar){ window.location.href=`${tipoEliminar}/eliminar.php?id=${idAEliminar}&volver=dashboard.php#${tipoEliminar}`; } }

// Ventas múltiples
function mostrarFormularioVenta(){ document.getElementById('formVentaMultiple').style.display='block'; window.scrollTo({top: document.getElementById('formVentaMultiple').offsetTop, behavior:'smooth'}); }
function cerrarFormularioVenta(){ document.getElementById('formVentaMultiple').style.display='none'; }
function agregarFila(){
  const tabla = document.getElementById('tablaVenta').getElementsByTagName('tbody')[0];
  const fila = tabla.rows[0].cloneNode(true);
  fila.querySelector('select').value='';
  fila.querySelector('input').value=1;
  fila.querySelector('.precioUnitario').innerText='0.00';
  fila.querySelector('.subtotal').innerText='0.00';
  tabla.appendChild(fila);
}
function eliminarFila(btn){
  const tabla = document.getElementById('tablaVenta').getElementsByTagName('tbody')[0];
  if(tabla.rows.length>1){ btn.closest('tr').remove(); actualizarTotales(); } else { alert('Debe haber al menos un producto en la venta.'); }
}
function actualizarPrecio(select){
  const precio = select.selectedOptions[0].dataset.precio || 0;
  const fila = select.closest('tr');
  fila.querySelector('.precioUnitario').innerText = parseFloat(precio).toFixed(2);
  actualizarSubtotal(fila.querySelector('input'));
}
function actualizarSubtotal(input){
  const fila = input.closest('tr');
  const precio = parseFloat(fila.querySelector('.precioUnitario').innerText) || 0;
  const cantidad = parseInt(input.value) || 0;
  const subtotal = precio * cantidad;
  fila.querySelector('.subtotal').innerText = subtotal.toFixed(2);
  actualizarTotales();
}
function actualizarTotales(){
  let subtotalTotal = 0;
  document.querySelectorAll('.subtotal').forEach(s => subtotalTotal += parseFloat(s.innerText));
  const igv = subtotalTotal * 0.18;
  const total = subtotalTotal + igv;
  document.getElementById('totalSubtotal').innerText = subtotalTotal.toFixed(2);
  document.getElementById('totalIGV').innerText = igv.toFixed(2);
  document.getElementById('totalVenta').innerText = total.toFixed(2);
}
</script>
</body>
</html>
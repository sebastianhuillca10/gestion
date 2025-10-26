<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../modelo/Producto.php";

$productoModel = new Producto($conn);
$productosLista = $productoModel->obtenerProductosPaginados('', 0, 1000); // traer todos los productos
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar Venta Múltiple</title>
<link rel="stylesheet" href="../public/css/estilos.css">
<style>
body { font-family: Arial, sans-serif; margin:0; background:#f0f2f5; }
.main-content { margin-left:250px; padding:20px; }
h2 { margin-bottom:20px; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; }
table, th, td { border:1px solid #ddd; }
th, td { padding:12px; text-align:center; }
th { background:#1877f2; color:white; }
select, input[type="number"], input[type="text"] { padding:6px; width:100%; border-radius:4px; border:1px solid #ccc; }
.btn-primary, .btn-clear, .btn-add { background:#007bff; color:white; padding:10px 15px; border:none; border-radius:6px; cursor:pointer; }
.btn-primary:hover { background:#0069d9; }
.btn-clear { background:#6c757d; }
.btn-clear:hover { background:#5a6268; }
.btn-add { background:#28a745; }
.btn-add:hover { background:#218838; }
.total-container { margin-top:20px; background:#fff; padding:15px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
</style>
</head>
<body>
<div class="main-content">
<h2>Registrar Venta Múltiple</h2>

<form method="POST" action="guardar_multiples.php">
<table id="tablaVenta">
<thead>
<tr>
<th>Producto</th>
<th>Cantidad</th>
<th>Precio Unitario</th>
<th>Subtotal</th>
<th>Acción</th>
</tr>
</thead>
<tbody>
<tr>
<td>
<select name="producto_id[]" onchange="actualizarPrecio(this)">
<option value="">Selecciona producto</option>
<?php foreach($productosLista as $prod): ?>
<option value="<?= $prod['id'] ?>" data-precio="<?= $prod['precio_venta'] ?>">
<?= htmlspecialchars($prod['nombre']) ?> (S/ <?= number_format($prod['precio_venta'],2) ?>)
</option>
<?php endforeach; ?>
</select>
</td>
<td><input type="number" name="cantidad[]" value="1" min="1" oninput="actualizarSubtotal(this)"></td>
<td class="precioUnitario">0.00</td>
<td class="subtotal">0.00</td>
<td><button type="button" onclick="eliminarFila(this)">Eliminar</button></td>
</tr>
</tbody>
</table>

<button type="button" class="btn-add" onclick="agregarFila()">Agregar Producto</button>

<div class="total-container">
<p>Subtotal: S/ <span id="totalSubtotal">0.00</span></p>
<p>IGV (18%): S/ <span id="totalIGV">0.00</span></p>
<p>Total: S/ <span id="totalVenta">0.00</span></p>

<label>Tipo Comprobante:
<select name="tipo_comprobante">
<option value="Boleta">Boleta</option>
<option value="Factura">Factura</option>
</select>
</label>
<label>N° Comprobante: <input type="text" name="numero_comprobante" required></label>
</div>

<button type="submit" class="btn-primary" style="margin-top:20px;">Registrar Venta Múltiple</button>
<a href="../dashboard.php#ventas" class="btn-clear" style="margin-top:20px;">Cancelar</a>
</form>
</div>

<script>
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
  if(tabla.rows.length>1){ btn.closest('tr').remove(); actualizarTotales(); } 
  else { alert('Debe haber al menos un producto en la venta.'); }
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
  fila.querySelector('.subtotal').innerText = (precio*cantidad).toFixed(2);
  actualizarTotales();
}

function actualizarTotales(){
  let subtotalTotal = 0;
  document.querySelectorAll('.subtotal').forEach(s => subtotalTotal += parseFloat(s.innerText));
  const igv = subtotalTotal * 0.18;
  document.getElementById('totalSubtotal').innerText = subtotalTotal.toFixed(2);
  document.getElementById('totalIGV').innerText = igv.toFixed(2);
  document.getElementById('totalVenta').innerText = (subtotalTotal + igv).toFixed(2);
}

// Inicializar totales
actualizarTotales();
</script>
</body>
</html>
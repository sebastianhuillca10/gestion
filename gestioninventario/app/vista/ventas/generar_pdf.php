<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Venta.php";
require_once __DIR__ . "/../../../public/fpdf/fpdf.php";

$ventaModel = new Venta($conn);

// Obtener ID de venta
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0){
    die("ID de venta inválido.");
}

// Datos de la venta
$venta = $ventaModel->obtenerVentaPorId($id);
if(!$venta){
    die("Venta no encontrada.");
}

// Detalles de la venta (productos)
$detalles = $ventaModel->obtenerDetallesVenta($id);
if(!$detalles){
    die("No hay productos en esta venta.");
}

// Datos de la empresa
$empresaNombre = "Mi Tienda S.A.C.";
$empresaRUC = "12345678901";
$empresaDireccion = "Av. Ejemplo 123, Lima - Perú";
$empresaTelefono = "(01) 123-4567";
$empresaEmail = "ventas@mitienda.com";
$condicionesVenta = "Todos los precios incluyen IGV (18%). No se aceptan devoluciones sin boleta/factura.";

// Datos del cliente (opcional)
$clienteNombre = $venta['cliente_nombre'] ?? "Cliente Genérico";
$clienteDocumento = $venta['cliente_documento'] ?? "DNI: 12345678";

// Crear PDF
class PDF extends FPDF {
    public $condiciones = '';
    function Footer() {
        $this->SetY(-30);
        $this->SetFont('Arial','I',9);
        $this->MultiCell(0,5,utf8_decode($this->condiciones),0,'C');
        $this->SetY(-15);
        $this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->condiciones = $condicionesVenta;
$pdf->AddPage();
$pdf->SetAutoPageBreak(true,35);

// LOGO
$logoPath = '../../public/img/logo.png';
if(file_exists($logoPath)){
    $pdf->Image($logoPath,10,10,40);
}

// Encabezado
$pdf->SetFont('Arial','B',16);
$pdf->SetTextColor(0,0,128);
$pdf->Cell(0,10, $venta['tipo_comprobante']==='Factura' ? 'FACTURA' : 'BOLETA DE VENTA',0,1,'C');
$pdf->Ln(2);

// Datos de la empresa
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,5,utf8_decode($empresaNombre),0,1,'C');
$pdf->Cell(0,5,"RUC: $empresaRUC",0,1,'C');
$pdf->Cell(0,5,utf8_decode($empresaDireccion),0,1,'C');
$pdf->Cell(0,5,"Tel: $empresaTelefono | Email: $empresaEmail",0,1,'C');
$pdf->Ln(5);

// Información de la venta
$pdf->SetFont('Arial','B',12);
$pdf->Cell(50,6,'Número de Comprobante:',0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,6,$venta['numero_comprobante'],0,1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(50,6,'Fecha:',0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,6,$venta['fecha'],0,1);
$pdf->Ln(3);

// Datos del cliente
$pdf->SetFont('Arial','B',12);
$pdf->Cell(50,6,'Cliente:',0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,6,utf8_decode($clienteNombre),0,1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(50,6,'Documento:',0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,6,$clienteDocumento,0,1);
$pdf->Ln(5);

// Tabla de productos
$pdf->SetFillColor(200,220,255);
$pdf->SetDrawColor(50,50,100);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(80,10,'Producto',1,0,'C',true);
$pdf->Cell(30,10,'Cantidad',1,0,'C',true);
$pdf->Cell(40,10,'Precio Unit.',1,0,'C',true);
$pdf->Cell(40,10,'Subtotal',1,1,'C',true);

$pdf->SetFont('Arial','',12);

$total_subtotal = 0;
$total_igv = 0;
$total_total = 0;

foreach($detalles as $d){
    $producto = utf8_decode($d['producto'] ?? $d['nombre_producto'] ?? 'Sin nombre');
    $cantidad = $d['cantidad'] ?? 0;
    $precio_unitario = $d['precio_unitario'] ?? $d['precio'] ?? 0;

    $subtotal = $cantidad * $precio_unitario;
    $igv = round($subtotal * 0.18,2);
    $total = $subtotal + $igv;

    $total_subtotal += $subtotal;
    $total_igv += $igv;
    $total_total += $total;

    $pdf->Cell(80,10,$producto,1,0);
    $pdf->Cell(30,10,$cantidad,1,0,'C');
    $pdf->Cell(40,10,'S/ '.number_format($precio_unitario,2),1,0,'R');
    $pdf->Cell(40,10,'S/ '.number_format($subtotal,2),1,1,'R');
}

// Totales
$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(120,8,'Subtotal:',0,0,'R');
$pdf->Cell(40,8,'S/ '.number_format($total_subtotal,2),1,1,'R');

$pdf->Cell(120,8,'IGV (18%):',0,0,'R');
$pdf->Cell(40,8,'S/ '.number_format($total_igv,2),1,1,'R');

$pdf->Cell(120,8,'Total:',0,0,'R');
$pdf->Cell(40,8,'S/ '.number_format($total_total,2),1,1,'R');

$pdf->Ln(10);
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,5,'¡Gracias por su compra!',0,1,'C');
$pdf->Cell(0,5,'Documento generado automáticamente.',0,1,'C');

// Salida PDF
$pdf->Output('I', $venta['tipo_comprobante'].'_'.$venta['numero_comprobante'].'.pdf');
?>
<?php
session_start();

// Verificar sesión y tipo de usuario
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: /gestioninventario/app/vista/login.php");
    exit();
}

// Incluir conexión y modelo Proveedor
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Proveedor.php";

$proveedorObj = new Proveedor($conn);

// Obtener ID del proveedor a editar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0){
    die("ID de proveedor inválido.");
}

// Obtener datos del proveedor
$proveedorData = $proveedorObj->obtenerProveedorPorId($id);
if(!$proveedorData){
    die("Proveedor no encontrado.");
}

// Inicializar variables para el formulario
$empresa  = $proveedorData['empresa'] ?? '';
$ruc      = $proveedorData['ruc'] ?? '';
$telefono = $proveedorData['telefono'] ?? '';
$email    = $proveedorData['email'] ?? '';
$direccion= $proveedorData['direccion'] ?? '';

$error = '';
// Usamos ruta absoluta para volver
$volver = '/gestioninventario/app/vista/dashboard.php#proveedores';

// Procesar actualización
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $empresa  = trim($_POST['empresa']);
    $ruc      = trim($_POST['ruc']);
    $telefono = trim($_POST['telefono']);
    $email    = trim($_POST['email']);
    $direccion= trim($_POST['direccion']);

    if(empty($empresa) || empty($ruc) || empty($telefono) || empty($email) || empty($direccion)){
        $error = "Todos los campos son obligatorios.";
    } else {
        if($proveedorObj->actualizarProveedor($id, $empresa, $ruc, $telefono, $email, $direccion)){
            header("Location: $volver");
            exit();
        } else {
            $error = "Error al actualizar el proveedor. Verifica los datos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Proveedor</title>
<link rel="stylesheet" href="/gestioninventario/public/css/estilos.css">
<style>
    body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
    .container { max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.1);}
    h2 { text-align: center; margin-bottom: 20px; color: #1877f2; }
    form label { display: block; margin-top: 15px; font-weight: bold; }
    form input, form textarea { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 14px; }
    button { margin-top: 20px; padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 16px; }
    button:hover { background-color: #218838; }
    .error { color: red; text-align: center; margin-bottom: 10px; }
    button.cancelar { background-color: #6c757d; margin-top: 10px; }
    button.cancelar:hover { background-color: #5a6268; }
</style>
</head>
<body>
<div class="container">
    <h2>Editar Proveedor</h2>

    <?php if(!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="<?= $_SERVER['PHP_SELF'] . '?id=' . $id ?>">
        <label>Empresa</label>
        <input type="text" name="empresa" value="<?= htmlspecialchars($empresa) ?>" required>

        <label>RUC</label>
        <input type="text" name="ruc" value="<?= htmlspecialchars($ruc) ?>" required>

        <label>Teléfono</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($telefono) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label>Dirección</label>
        <textarea name="direccion" rows="3" required><?= htmlspecialchars($direccion) ?></textarea>

        <button type="submit">Actualizar Proveedor</button>
        <button type="button" class="cancelar" onclick="window.location.href='<?= $volver ?>'">Cancelar</button>
    </form>
</div>
</body>
</html>
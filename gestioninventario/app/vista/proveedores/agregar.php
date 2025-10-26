<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../modelo/Proveedor.php";

$error = '';
$success = '';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $empresa = $_POST['empresa'];
    $ruc = $_POST['ruc'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];

    $proveedorModel = new Proveedor($conn); // Asumiendo que tu clase Proveedor necesita $conn
    if($proveedorModel->registrarProveedor($empresa, $ruc, $telefono, $email, $direccion)){
        $success = "Proveedor agregado correctamente.";
    } else {
        $error = "Error al agregar proveedor.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Proveedor - Admin</title>
    <link rel="stylesheet" href="/gestioninventario/app/assets/css/estilos.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        form input {
            width: 100%;
            padding: 10px 12px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        form button {
            width: 100%;
            padding: 12px;
            background-color: #1877f2;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        form button:hover {
            background-color: #155db2;
        }

        .message {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .error { color: #e74c3c; }
        .success { color: #28a745; }

        a.back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #555;
            text-decoration: none;
        }

        a.back-link:hover {
            text-decoration: underline;
            color: #1877f2;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Agregar Nuevo Proveedor</h2>

        <?php if($error != ''): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if($success != ''): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="empresa" placeholder="Nombre de la empresa" required>
            <input type="text" name="ruc" placeholder="RUC" required>
            <input type="text" name="telefono" placeholder="Teléfono" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="text" name="direccion" placeholder="Dirección" required>
            <button type="submit">Agregar Proveedor</button>
        </form>

        <a class="back-link" href="../dashboard.php">← Volver al Dashboard</a>
    </div>
</body>
</html>
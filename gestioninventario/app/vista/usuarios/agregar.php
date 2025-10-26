<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../app/modelo/Usuario.php";

$error = '';
$success = '';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    $telefono = $_POST['telefono'];
    $genero = $_POST['genero'];
    $tipo = $_POST['tipo'];

    $usuarioModel = new Usuario($conn);
    if($usuarioModel->registrarUsuario($nombre, $email, $usuario, $password, $telefono, $genero, $tipo)){
        $success = "Usuario agregado correctamente.";
    } else {
        $error = "Error al agregar usuario.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Usuario - Admin</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
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

        form input, form select {
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
        <h2>Agregar Nuevo Usuario</h2>

        <?php if($error != ''): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if($success != ''): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="text" name="telefono" placeholder="Teléfono">
            <select name="genero" required>
                <option value="">Seleccione género</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
                <option value="Otro">Otro</option>
            </select>
            <select name="tipo" required>
                <option value="usuario">Usuario</option>
                <option value="admin">Administrador</option>
            </select>
            <button type="submit">Agregar Usuario</button>
        </form>

        <a class="back-link" href="../dashboard.php">← Volver al Dashboard</a>
    </div>
</body>
</html>
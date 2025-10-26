<?php
session_start();

// Verificar sesión y tipo de usuario
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: ../../login.php");
    exit();
}

// Rutas correctas según tu estructura
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../modelo/Usuario.php";

$usuarioObj = new Usuario($conn);

// Obtener ID del usuario a editar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id <= 0){
    die("ID de usuario inválido.");
}

// Obtener datos del usuario
$usuarioData = $usuarioObj->obtenerUsuarioPorId($id);

if(!$usuarioData){
    die("Usuario no encontrado.");
}

// Inicializar variables para el formulario
$nombre   = $usuarioData['nombre'] ?? '';
$email    = $usuarioData['email'] ?? '';
$usuarioN = $usuarioData['usuario'] ?? '';
$telefono = $usuarioData['telefono'] ?? '';
$genero   = $usuarioData['genero'] ?? '';
$tipo     = $usuarioData['tipo'] ?? 'usuario';

$error = '';

// Procesar actualización
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nombre   = trim($_POST['nombre']);
    $email    = trim($_POST['email']);
    $usuarioN = trim($_POST['usuario']);
    $telefono = trim($_POST['telefono']);
    $genero   = $_POST['genero'] ?? '';
    $tipo     = $_POST['tipo'] ?? 'usuario';

    // Validación básica
    if(empty($nombre) || empty($email) || empty($usuarioN) || empty($telefono)){
        $error = "Todos los campos son obligatorios.";
    } else {
        // Actualizar usuario
        if($usuarioObj->actualizarUsuario($id, $nombre, $email, $usuarioN, $telefono, $genero, $tipo)){
            header("Location: ../dashboard.php"); // Redirige al dashboard
            exit();
        } else {
            $error = "Error al actualizar el usuario. Verifica los datos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Usuario</title>
<link rel="stylesheet" href="../../public/css/estilos.css">
<style>
    body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
    .container { max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.1);}
    h2 { text-align: center; margin-bottom: 20px; color: #1877f2; }
    form label { display: block; margin-top: 15px; font-weight: bold; }
    form input, form select { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; }
    button { margin-top: 20px; padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 16px; }
    button:hover { background-color: #218838; }
    .error { color: red; text-align: center; margin-bottom: 10px; }
</style>
</head>
<body>
<div class="container">
    <h2>Editar Usuario</h2>

    <?php if(!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label>Usuario</label>
        <input type="text" name="usuario" value="<?= htmlspecialchars($usuarioN) ?>" required>

        <label>Teléfono</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($telefono) ?>" required>

        <label>Género</label>
        <select name="genero" required>
            <option value="Masculino" <?= $genero === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
            <option value="Femenino"  <?= $genero === 'Femenino'  ? 'selected' : '' ?>>Femenino</option>
        </select>

        <label>Tipo</label>
        <select name="tipo" required>
            <option value="usuario" <?= $tipo === 'usuario' ? 'selected' : '' ?>>Usuario</option>
            <option value="admin"   <?= $tipo === 'admin'   ? 'selected' : '' ?>>Admin</option>
        </select>

        <button type="submit">Actualizar Usuario</button>
    </form>
</div>
</body>
</html>
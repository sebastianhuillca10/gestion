<?php
session_start();
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../modelo/Usuario.php";

if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'admin'){
    header("Location: login.php");
    exit();
}

$usuarioModel = new Usuario($conn);

// Obtener todos los usuarios
$usuarios = $usuarioModel->obtenerTodosUsuarios();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Admin</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; }
        .container { margin-left: 240px; padding: 20px; }
        h1 { color: #1877f2; text-align: center; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #1877f2; color: #fff; }
        tr:hover { background-color: #f1f1f1; }
        .btn { padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-edit { background-color: #27ae60; color: #fff; }
        .btn-delete { background-color: #e74c3c; color: #fff; }
        .btn-add { background-color: #1877f2; color: #fff; margin-bottom: 10px; }
        .search { margin-bottom: 15px; width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 16px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Gestión de Usuarios</h1>

    <input type="text" id="search" class="search" placeholder="Buscar usuario..." onkeyup="buscarUsuarios()">

    <button class="btn btn-add" onclick="mostrarFormulario('add')">Agregar Usuario</button>

    <table id="tabla-usuarios">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Género</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($usuarios as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                <td><?php echo htmlspecialchars($u['usuario']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo htmlspecialchars($u['telefono']); ?></td>
                <td><?php echo htmlspecialchars($u['genero']); ?></td>
                <td><?php echo htmlspecialchars($u['tipo']); ?></td>
                <td>
                    <button class="btn btn-edit" onclick="editarUsuario(<?php echo $u['id']; ?>)">Editar</button>
                    <button class="btn btn-delete" onclick="eliminarUsuario(<?php echo $u['id']; ?>)">Eliminar</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function buscarUsuarios(){
    let input = document.getElementById("search");
    let filter = input.value.toLowerCase();
    let rows = document.querySelectorAll("#tabla-usuarios tbody tr");

    rows.forEach(row => {
        let nombre = row.cells[1].textContent.toLowerCase();
        let usuario = row.cells[2].textContent.toLowerCase();
        let email = row.cells[3].textContent.toLowerCase();
        row.style.display = (nombre.includes(filter) || usuario.includes(filter) || email.includes(filter)) ? "" : "none";
    });
}

function mostrarFormulario(tipo){
    alert("Aquí se mostraría el formulario para " + tipo + " usuario");
}

function editarUsuario(id){
    alert("Editar usuario con ID: " + id);
}

function eliminarUsuario(id){
    if(confirm("¿Seguro que deseas eliminar este usuario?")){
        alert("Eliminar usuario con ID: " + id);
        // Aquí se llamaría a un controlador PHP para eliminar
    }
}
</script>
</body>
</html>
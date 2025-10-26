<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['tipo'] != 'usuario'){
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuario - Inventario Market San Carlos</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
        }
        .header {
            background-color: #1877f2;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .content {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        .welcome {
            text-align: center;
            margin-bottom: 20px;
        }
        .menu {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        .menu a {
            text-decoration: none;
            color: #1877f2;
            font-weight: bold;
            padding: 10px 20px;
            border: 1px solid #1877f2;
            border-radius: 6px;
            transition: 0.3s;
        }
        .menu a:hover {
            background-color: #1877f2;
            color: #fff;
        }
        .section {
            text-align: center;
        }
        a.logout {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #E74C3C;
            font-weight: bold;
            text-decoration: none;
        }
        a.logout:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Usuario - Inventario Market San Carlos</h1>
    </div>
    <div class="content">
        <div class="welcome">
            <h2>Bienvenido, <?php echo $_SESSION['usuario']; ?>!</h2>
            <p>Este es tu panel de usuario normal.</p>
        </div>

        <div class="menu">
            <a href="#" onclick="mostrarSeccion('mis-productos')">Mis Productos</a>
            <a href="#" onclick="mostrarSeccion('mis-ventas')">Mis Ventas</a>
            <a href="#" onclick="mostrarSeccion('perfil')">Perfil</a>
        </div>

        <div id="mis-productos" class="section" style="display:none;">
            <h3>Mis Productos</h3>
            <p>Aquí puedes ver tus productos asignados.</p>
        </div>

        <div id="mis-ventas" class="section" style="display:none;">
            <h3>Mis Ventas</h3>
            <p>Aquí puedes ver tus ventas realizadas.</p>
        </div>

        <div id="perfil" class="section" style="display:none;">
            <h3>Perfil de Usuario</h3>
            <p>Actualiza tu información personal aquí.</p>
        </div>

        <a class="logout" href="logout.php">Cerrar sesión</a>
    </div>

    <script>
        function mostrarSeccion(seccionId) {
            const secciones = document.querySelectorAll('.section');
            secciones.forEach(s => s.style.display = 'none');
            document.getElementById(seccionId).style.display = 'block';
        }
    </script>
</body>
</html>
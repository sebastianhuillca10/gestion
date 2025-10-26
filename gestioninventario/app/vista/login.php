<?php
session_start();
$error = "";
$success = "";
if(isset($_GET['error'])){
    $error = $_GET['error'];
}
if(isset($_GET['success'])){
    $success = $_GET['success'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Inventario Market San Carlos</title>
    <link rel="stylesheet" href="/gestioninventario/public/css/estilos.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f0f2f5;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            padding: 40px;
            width: 450px;
        }

        h1 {
            color: #1877f2;
            text-align: center;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #1877f2;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #155db2;
        }

        .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
        }

        .success {
            color: green;
            margin-bottom: 10px;
            text-align: center;
        }

        .register-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #1877f2;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Inventario Market San Carlos</h1>

            <?php if($error != ""): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if($success != ""): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form action="/gestioninventario/app/controlador/LoginController.php" method="POST">
                <input type="text" name="usuario" placeholder="Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit">Iniciar sesión</button>
            </form>

            <a class="register-link" href="/gestioninventario/app/vista/registro.php">¿No tienes cuenta? Regístrate</a>
        </div>
    </div>
</body>
</html>
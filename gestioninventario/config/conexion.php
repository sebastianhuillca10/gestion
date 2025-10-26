<?php
// Datos de conexión
$host = "localhost";
$user = "root";
$pass = ""; // si tienes contraseña de root, colócala aquí
$db   = "gestioninventario";

// Crear conexión
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
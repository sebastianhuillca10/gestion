<?php
class Usuario {
    private $conn;

    public function __construct($conexion){
        $this->conn = $conexion;
    }

    /* ======================================================
       🔹 Registrar nuevo usuario
    ====================================================== */
    public function registrarUsuario($nombre, $email, $usuario, $password, $telefono, $genero, $tipo = 'usuario'){
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nombre, email, usuario, password, telefono, genero, tipo)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssss", $nombre, $email, $usuario, $password_hashed, $telefono, $genero, $tipo);
        return $stmt->execute();
    }

    /* ======================================================
       🔹 Validar login (usuario + contraseña)
    ====================================================== */
    public function validarUsuario($usuario, $password){
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE usuario = ? LIMIT 1");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if($resultado && $resultado->num_rows === 1){
            $fila = $resultado->fetch_assoc();
            if(password_verify($password, $fila['password'])){
                return $fila;
            }
        }
        return false;
    }

    /* ======================================================
       🔹 Contar usuarios (para paginación)
    ====================================================== */
    public function contarUsuarios($busqueda = ''){
        if(!empty($busqueda)){
            $busquedaLike = "%$busqueda%";
            $sql = "SELECT COUNT(*) as total FROM usuarios 
                    WHERE nombre LIKE ? OR usuario LIKE ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $busquedaLike, $busquedaLike);
            $stmt->execute();
            $resultado = $stmt->get_result()->fetch_assoc();
            return $resultado['total'] ?? 0;
        } else {
            $sql = "SELECT COUNT(*) as total FROM usuarios";
            $resultado = $this->conn->query($sql)->fetch_assoc();
            return $resultado['total'] ?? 0;
        }
    }

    /* ======================================================
       🔹 Obtener usuarios paginados (con o sin búsqueda)
    ====================================================== */
    public function obtenerUsuariosPaginados($busqueda = '', $inicio = 0, $por_pagina = 5){
        $busquedaLike = "%$busqueda%";
        $sql = "SELECT * FROM usuarios 
                WHERE nombre LIKE ? OR usuario LIKE ? 
                ORDER BY id ASC 
                LIMIT ?, ?";
        $stmt = $this->conn->prepare($sql);

        // 🔹 Los dos últimos deben ser enteros ("ii")
        $stmt->bind_param("ssii", $busquedaLike, $busquedaLike, $inicio, $por_pagina);
        $stmt->execute();
        $resultado = $stmt->get_result();

        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /* ======================================================
       🔹 Obtener todos los usuarios (sin límite)
       (solo usar en casos especiales)
    ====================================================== */
    public function obtenerUsuarios(){
        $sql = "SELECT * FROM usuarios ORDER BY id ASC";
        $resultado = $this->conn->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /* ======================================================
       🔹 Obtener usuario por ID
    ====================================================== */
    public function obtenerUsuarioPorId($id){
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc() ?? null;
    }

    /* ======================================================
       🔹 Actualizar usuario
    ====================================================== */
    public function actualizarUsuario($id, $nombre, $email, $usuario, $telefono, $genero, $tipo){
        $sql = "UPDATE usuarios 
                SET nombre = ?, email = ?, usuario = ?, telefono = ?, genero = ?, tipo = ? 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssi", $nombre, $email, $usuario, $telefono, $genero, $tipo, $id);
        return $stmt->execute();
    }

    /* ======================================================
       🔹 Eliminar usuario
    ====================================================== */
    public function eliminarUsuario($id){
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
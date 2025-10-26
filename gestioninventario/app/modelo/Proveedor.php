<?php
require_once __DIR__ . '/../../config/conexion.php';

class Proveedor {
    private $conn;

    public function __construct($conexion){
        $this->conn = $conexion;
    }

    // Registrar nuevo proveedor
    public function registrarProveedor($empresa, $ruc, $telefono, $email, $direccion){
        $stmt = $this->conn->prepare("
            INSERT INTO proveedores (empresa, ruc, telefono, email, direccion)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $empresa, $ruc, $telefono, $email, $direccion);
        return $stmt->execute();
    }

    // Contar proveedores
    public function contarProveedores($busqueda = ''){
        if(!empty($busqueda)){
            $sql = "SELECT COUNT(*) as total FROM proveedores WHERE empresa LIKE ? OR ruc LIKE ?";
            $param = "%$busqueda%";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $param, $param);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            return $res['total'] ?? 0;
        } else {
            $result = $this->conn->query("SELECT COUNT(*) as total FROM proveedores");
            $fila = $result->fetch_assoc();
            return $fila['total'] ?? 0;
        }
    }

    // Obtener proveedores paginados
    public function obtenerProveedoresPaginados($busqueda = '', $inicio = 0, $por_pagina = 5){
        $sql = "SELECT * FROM proveedores WHERE empresa LIKE ? OR ruc LIKE ? ORDER BY id ASC LIMIT ?, ?";
        $param = "%$busqueda%";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $param, $param, $inicio, $por_pagina);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener un proveedor por ID
    public function obtenerProveedorPorId($id){
        $stmt = $this->conn->prepare("SELECT * FROM proveedores WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Actualizar proveedor
    public function actualizarProveedor($id, $empresa, $ruc, $telefono, $email, $direccion){
        $stmt = $this->conn->prepare("
            UPDATE proveedores 
            SET empresa=?, ruc=?, telefono=?, email=?, direccion=? 
            WHERE id=?
        ");
        $stmt->bind_param("sssssi", $empresa, $ruc, $telefono, $email, $direccion, $id);
        return $stmt->execute();
    }

    // Eliminar proveedor
    public function eliminarProveedor($id){
        $stmt = $this->conn->prepare("DELETE FROM proveedores WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
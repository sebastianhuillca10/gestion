<?php
class Venta {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    // 🔹 Contar todas las ventas registradas
    public function contarVentas() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM ventas");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        return $fila['total'] ?? 0;
    }

    // 🔹 Registrar nueva venta (una fila por producto)
    public function registrarVenta($productos, $tipo_comprobante = 'Boleta') {
    try {
        $this->conn->begin_transaction();
        $ultimo_id = 0;

        foreach($productos as $prod){
            $subtotal = $prod['cantidad'] * $prod['precio_unitario'];
            $igv = round($subtotal * 0.18, 2);
            $total = $subtotal + $igv;

            // Generar número de comprobante
            $prefijo = ($tipo_comprobante === 'Factura') ? 'F' : 'B';
            $numero = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $numero_comprobante = $prefijo . date('Y') . '-' . $numero;

            // Insertar venta
            $stmt = $this->conn->prepare("INSERT INTO ventas (producto_id, cantidad, precio_unitario, subtotal, igv, total, tipo_comprobante, numero_comprobante, fecha) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param(
                "iidddsss",
                $prod['id'],
                $prod['cantidad'],
                $prod['precio_unitario'],
                $subtotal,
                $igv,
                $total,
                $tipo_comprobante,
                $numero_comprobante
            );
            $stmt->execute();

            // Guardar el último ID insertado
            $ultimo_id = $this->conn->insert_id;

            // Reducir stock
            $stmtStock = $this->conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            $stmtStock->bind_param("ii", $prod['cantidad'], $prod['id']);
            $stmtStock->execute();
        }

        $this->conn->commit();
        return $ultimo_id; // ✅ Devolvemos el ID de la última venta registrada

    } catch(Exception $e) {
        $this->conn->rollback();
        return false;
    }
}

    // 🔹 Obtener todas las ventas
    public function obtenerVentaPorId($id) {
        $stmt = $this->conn->prepare("
            SELECT v.*, p.nombre AS producto
            FROM ventas v
            JOIN productos p ON v.producto_id = p.id
            WHERE v.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

     // 🔹 Obtener todos los detalles de una venta múltiple (mismo número de comprobante)
    public function obtenerDetallesVenta($id) {
        // Primero, buscamos el número de comprobante de esa venta
        $stmt = $this->conn->prepare("SELECT numero_comprobante FROM ventas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $venta = $stmt->get_result()->fetch_assoc();

        if(!$venta) return [];

        $numero_comprobante = $venta['numero_comprobante'];

        // Luego obtenemos todas las ventas con ese mismo comprobante
        $stmt2 = $this->conn->prepare("
            SELECT v.*, p.nombre AS producto
            FROM ventas v
            JOIN productos p ON v.producto_id = p.id
            WHERE v.numero_comprobante = ?
        ");
        $stmt2->bind_param("s", $numero_comprobante);
        $stmt2->execute();
        $resultado = $stmt2->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }


    // 🔹 Eliminar venta y restaurar stock
    public function eliminarVenta($venta_id) {
        // Obtener cantidad y producto_id de la venta
        $stmtSel = $this->conn->prepare("SELECT cantidad, producto_id FROM ventas WHERE id = ?");
        $stmtSel->bind_param("i", $venta_id);
        $stmtSel->execute();
        $venta = $stmtSel->get_result()->fetch_assoc();

        if(!$venta) return false;

        // Restaurar stock
        $stmtStock = $this->conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $stmtStock->bind_param("ii", $venta['cantidad'], $venta['producto_id']);
        $stmtStock->execute();

        // Eliminar venta
        $stmt = $this->conn->prepare("DELETE FROM ventas WHERE id = ?");
        $stmt->bind_param("i", $venta_id);
        return $stmt->execute();
    }
}
?>
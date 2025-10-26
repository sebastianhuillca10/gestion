<?php
class Producto {
    private $conn;

    public function __construct($conexion){
        $this->conn = $conexion;
    }

    // Contar productos con búsqueda opcional
    public function contarProductos($busqueda = '') {
        if($busqueda){
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM productos WHERE nombre LIKE ? OR categoria LIKE ?");
            $like = "%$busqueda%";
            $stmt->bind_param("ss", $like, $like);
        } else {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM productos");
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        return $fila['total'] ?? 0;
    }

    // Obtener productos paginados con búsqueda opcional
    public function obtenerProductosPaginados($busqueda = '', $inicio = 0, $limite = 15){
        if($busqueda){
            $stmt = $this->conn->prepare("SELECT * FROM productos WHERE nombre LIKE ? OR categoria LIKE ? ORDER BY nombre ASC LIMIT ?, ?");
            $like = "%$busqueda%";
            $stmt->bind_param("ssii", $like, $like, $inicio, $limite);
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM productos ORDER BY nombre ASC LIMIT ?, ?");
            $stmt->bind_param("ii", $inicio, $limite);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener un producto por ID
    public function obtenerProductoPorId($id){
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Agregar producto
    public function agregarProducto($nombre, $categoria, $inventariado, $precio_compra, $precio_venta, $stock){
        $stmt = $this->conn->prepare(
            "INSERT INTO productos (nombre, categoria, inventariado, precio_compra, precio_venta, stock) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssddi", $nombre, $categoria, $inventariado, $precio_compra, $precio_venta, $stock);
        return $stmt->execute();
    }

    // Actualizar producto
    public function actualizarProducto($id, $nombre, $categoria, $inventariado, $precio_compra, $precio_venta, $stock){
        $stmt = $this->conn->prepare(
            "UPDATE productos SET nombre=?, categoria=?, inventariado=?, precio_compra=?, precio_venta=?, stock=? WHERE id=?"
        );
        $stmt->bind_param("sssddii", $nombre, $categoria, $inventariado, $precio_compra, $precio_venta, $stock, $id);
        return $stmt->execute();
    }

    // Eliminar producto
    public function eliminarProducto($id){
        $stmt = $this->conn->prepare("DELETE FROM productos WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Listar todas las categorías
    public function listarCategorias(){
        $stmt = $this->conn->prepare("SELECT nombre FROM categorias ORDER BY nombre ASC");
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener solo productos con stock disponible (>0)
    public function obtenerProductosDisponibles(){
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE stock > 0 ORDER BY nombre ASC");
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}
?>
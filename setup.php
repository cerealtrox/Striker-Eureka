<?php
// setup.php
try {
    $db = new SQLite3('database.sqlite');
    $db->enableExceptions(true);

    // Tabla de Clientes (para control de cuentas por cobrar)
    $db->exec("CREATE TABLE IF NOT EXISTS clientes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        telefono TEXT,
        saldo_pendiente REAL DEFAULT 0.0
    )");

    // Tabla de Choferes
    $db->exec("CREATE TABLE IF NOT EXISTS choferes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        licencia TEXT NOT NULL,
        estatus TEXT DEFAULT 'Disponible' -- Disponible, En Ruta, Inactivo
    )");

    // Tabla de Vehículos
    $db->exec("CREATE TABLE IF NOT EXISTS unidades (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        placas TEXT NOT NULL,
        modelo TEXT,
        capacidad_toneladas REAL,
        estatus TEXT DEFAULT 'Disponible' -- Disponible, En Ruta, Mantenimiento
    )");

    // Tabla de Fletes (Transacciones y control de tránsito)
    $db->exec("CREATE TABLE IF NOT EXISTS fletes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cliente_id INTEGER,
        chofer_id INTEGER,
        unidad_id INTEGER,
        origen TEXT NOT NULL,
        destino TEXT NOT NULL,
        costo REAL NOT NULL,
        estatus TEXT DEFAULT 'Pendiente', -- Pendiente, En Tránsito, Entregado, Pagado
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(cliente_id) REFERENCES clientes(id),
        FOREIGN KEY(chofer_id) REFERENCES choferes(id),
        FOREIGN KEY(unidad_id) REFERENCES unidades(id)
    )");

    // Seed de datos básicos para pruebas
    $db->exec("INSERT INTO clientes (nombre, telefono) VALUES ('Logística del Norte', '555-0101')");
    $db->exec("INSERT INTO choferes (nombre, licencia) VALUES ('Juan Pérez', 'LIC-98765')");
    $db->exec("INSERT INTO unidades (placas, modelo, capacidad_toneladas) VALUES ('XYZ-123', 'Kenworth T680', 20.5)");

    echo "Base de datos generada e inicializada correctamente con datos semilla.";
} catch (Exception $e) {
    echo "Error al crear la base de datos: " . $e->getMessage();
}
?>

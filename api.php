<?php
// api.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

$db = new SQLite3('database.sqlite');
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Router básico
switch ($action) {
    case 'get_dashboard_stats':
        if ($method === 'GET') {
            // Ejemplo rápido para validar la conexión
            $clientes = $db->querySingle("SELECT COUNT(*) FROM clientes");
            echo json_encode(["status" => "success", "total_clientes" => $clientes]);
        }
        break;
        
    // Aquí agregaremos las rutas de la Fase 2 (CRUDs de Choferes, Unidades, Clientes)
    
    default:
        http_response_code(404);
        echo json_encode(["error" => "Acción no encontrada"]);
        break;
}
?>

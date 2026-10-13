<?php
// api.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

$db = new SQLite3('database.sqlite');
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Leer JSON del body
$input = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'get_dashboard_stats':
        $clientes = $db->querySingle("SELECT COUNT(*) FROM clientes");
        echo json_encode(["status" => "success", "total_clientes" => $clientes]);
        break;

    // --- CRUD CHOFERES  ---
    case 'choferes':
        if ($method === 'GET') {
            $result = $db->query("SELECT * FROM choferes");
            $choferes = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $choferes[] = $row; }
            echo json_encode($choferes);
        } elseif ($method === 'POST') {
            $stmt = $db->prepare("INSERT INTO choferes (nombre, licencia) VALUES (:nombre, :licencia)");
            $stmt->bindValue(':nombre', $input['nombre'], SQLITE3_TEXT);
            $stmt->bindValue(':licencia', $input['licencia'], SQLITE3_TEXT);
            $stmt->execute();
            echo json_encode(["status" => "success"]);
        }
        break;

    // --- CRUD UNIDADES  ---
    case 'unidades':
        if ($method === 'GET') {
            $result = $db->query("SELECT * FROM unidades");
            $unidades = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $unidades[] = $row; }
            echo json_encode($unidades);
        } elseif ($method === 'POST') {
            $stmt = $db->prepare("INSERT INTO unidades (placas, modelo, capacidad_toneladas) VALUES (:placas, :modelo, :capacidad)");
            $stmt->bindValue(':placas', $input['placas'], SQLITE3_TEXT);
            $stmt->bindValue(':modelo', $input['modelo'], SQLITE3_TEXT);
            $stmt->bindValue(':capacidad', $input['capacidad'], SQLITE3_FLOAT);
            $stmt->execute();
            echo json_encode(["status" => "success"]);
        }
        break;

    // --- CRUD CLIENTES  ---
    case 'clientes':
        if ($method === 'GET') {
            $result = $db->query("SELECT * FROM clientes");
            $clientes = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $clientes[] = $row; }
            echo json_encode($clientes);
        } elseif ($method === 'POST') {
            $stmt = $db->prepare("INSERT INTO clientes (nombre, telefono) VALUES (:nombre, :telefono)");
            $stmt->bindValue(':nombre', $input['nombre'], SQLITE3_TEXT);
            $stmt->bindValue(':telefono', $input['telefono'], SQLITE3_TEXT);
            $stmt->execute();
            echo json_encode(["status" => "success"]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Acción no encontrada"]);
        break;
}
?>

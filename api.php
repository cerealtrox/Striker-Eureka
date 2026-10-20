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

// --- CRUD FLETES Y CAMBIOS DE ESTATUS ---
    case 'fletes':
        if ($method === 'GET') {
            // filtra opcionalmente  fletes por pagar o activos
            $filtro = $_GET['filtro'] ?? '';
            $sql = "SELECT f.*, c.nombre as cliente_nombre, ch.nombre as chofer_nombre, u.placas as unidad_placas 
                    FROM fletes f 
                    LEFT JOIN clientes c ON f.cliente_id = c.id 
                    LEFT JOIN choferes ch ON f.chofer_id = ch.id 
                    LEFT JOIN unidades u ON f.unidad_id = u.id";
            
            if ($filtro === 'por_pagar') {
                $sql .= " WHERE f.estatus = 'Entregado'";
            }

            $result = $db->query($sql);
            $fletes = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $fletes[] = $row; }
            echo json_encode($fletes);
        } elseif ($method === 'POST') {
            // Crear flete
            $stmt = $db->prepare("INSERT INTO fletes (cliente_id, chofer_id, unidad_id, origen, destino, costo, estatus) 
                                  VALUES (:cliente_id, :chofer_id, :unidad_id, :origen, :destino, :costo, 'Pendiente')");
            $stmt->bindValue(':cliente_id', $input['cliente_id'], SQLITE3_INTEGER);
            $stmt->bindValue(':chofer_id', $input['chofer_id'], SQLITE3_INTEGER);
            $stmt->bindValue(':unidad_id', $input['unidad_id'], SQLITE3_INTEGER);
            $stmt->bindValue(':origen', $input['origen'], SQLITE3_TEXT);
            $stmt->bindValue(':destino', $input['destino'], SQLITE3_TEXT);
            $stmt->bindValue(':costo', $input['costo'], SQLITE3_FLOAT);
            $stmt->execute();

            // Actualizar estatus de chofer y unidad a 'En Ruta'
            $db->exec("UPDATE choferes SET estatus = 'En Ruta' WHERE id = " . intval($input['chofer_id']));
            $db->exec("UPDATE unidades SET estatus = 'En Ruta' WHERE id = " . intval($input['unidad_id']));

            echo json_encode(["status" => "success"]);
        } elseif ($method === 'PUT') {
            // Actualizar estatus del flete (En Tránsito, Entregado, Pagado)
            $flete_id = intval($input['id']);
            $nuevo_estatus = $input['estatus'];

            $stmt = $db->prepare("UPDATE fletes SET estatus = :estatus WHERE id = :id");
            $stmt->bindValue(':estatus', $nuevo_estatus, SQLITE3_TEXT);
            $stmt->bindValue(':id', $flete_id, SQLITE3_INTEGER);
            $stmt->execute();

            // Si el flete pasa a Entregado o Pagado, podemos liberar la unidad y chofer
            if ($nuevo_estatus === 'Entregado' || $nuevo_estatus === 'Pagado') {
                $fleteInfo = $db->querySingle("SELECT chofer_id, unidad_id, costo, cliente_id FROM fletes WHERE id = $flete_id", true);
                if ($fleteInfo) {
                    $db->exec("UPDATE choferes SET estatus = 'Disponible' WHERE id = " . $fleteInfo['chofer_id']);
                    $db->exec("UPDATE unidades SET estatus = 'Disponible' WHERE id = " . $fleteInfo['unidad_id']);

                    // Si se marca como pagado, incrementamos el saldo pendiente o lo liquidamos según lógica de cuentas por cobrar
                    if ($nuevo_estatus === 'Pagado') {
                        $db->exec("UPDATE clientes SET saldo_pendiente = saldo_pendiente + " . floatval($fleteInfo['costo']) . " WHERE id = " . $fleteInfo['cliente_id']);
                    }
                }
            }

            echo json_encode(["status" => "success"]);
        }
        break;
    
    default:
        http_response_code(404);
        echo json_encode(["error" => "Acción no encontrada"]);
        break;
}
?>

<?php
require_once '../app/controllers/CoffeeController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $userId = isset($_GET['id']) ? $_GET['id'] : null;
    $token = isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : null;
    $date = isset($requestData['date']) ? $requestData['date'] : null;

    if (!empty($userId) && !empty($token) && !empty($date)) {
        $coffeeController = new CoffeeController();
        
        $consumptionData = $coffeeController->getDailyUserConsumption($userId, $token, $requestData['date']);
        if (empty($consumptionData)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar consumo diário']);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $consumptionData]);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
        exit;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método HTTP não suportado']);
}
?>
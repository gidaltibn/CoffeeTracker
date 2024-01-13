<?php
require_once '../app/controllers/UserController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $date = isset($requestData['days']) ? $requestData['days'] : null;

    if (!empty($date)) {
        $userController = new UserController();

        $dailyRanking = $userController->getTopCoffeeConsumersLastXDays($date);

        if ($dailyRanking != false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $dailyRanking]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Ei, Ranking não encontrado']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método HTTP não suportado']);
    exit;
}

?>
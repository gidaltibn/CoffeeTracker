<?php
require_once '../app/models/CoffeeConsumption.php';
require_once '../app/controllers/CoffeeController.php';
require_once '../app/controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true);

    if (isset($requestData['user_id']) && isset($requestData['quantity']) && isset($requestData['consumption_date_time'])) {
        $token = isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : null;
        $userId = $requestData['user_id'];

        $authController = new AuthController();

        if ($authController->validateToken($token, $userId)) { 
            $coffeeController = new CoffeeController();
            $createdCoffee = $coffeeController->createCoffee($requestData);

            if ($createdCoffee) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Consumo de café criado com sucesso']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Falha ao criar o consumo de café']);
                exit;
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token inválido']);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Dados insuficientes para criar o consumo de café']);
        exit;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método HTTP não suportado']);
    exit;
}

?>
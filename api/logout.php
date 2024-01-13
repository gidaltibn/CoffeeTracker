<?php
require_once '../app/controllers/UserController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true);

    if (isset($requestData['id'])) {
        $userController = new UserController();

        $userData = $userController->logoutUser($requestData['id']);
        if ($userData !== false) {
            header('Content-Type: application/json');
            exit;
        } else {
            header('Content-Type: application/json');
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID do usuário não informado']);
        exit;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método HTTP não suportado']);
    exit;
}

?>
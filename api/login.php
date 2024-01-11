<?php
require_once '../app/controllers/UserController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true);

    if (isset($requestData['email']) && isset($requestData['password'])) {
        $userController = new UserController();

        $userData = $userController->loginUser($requestData['email'], $requestData['password']);

        if ($userData !== false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'user' => $userData]);
            exit;
        } else {
            header('Content-Type: application/json');
            exit;
        }
    } else {
        header('Content-Type: application/json');
        exit;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método HTTP não suportado']);
    exit;
}
?>
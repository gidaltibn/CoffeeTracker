<?php

require_once '../app/models/User.php';
require_once '../app/controllers/UserController.php';
require_once '../api/users_list.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $requestData = json_decode(file_get_contents('php://input'), true);

    if (isset($requestData['name']) && isset($requestData['email']) && isset($requestData['password'])) {
        $userController = new UserController();

        $createdUser = $userController->createUser($requestData);

        if ($createdUser) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso']);
            exit;
        } else {
            header('Content-Type: application/json');
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Dados insuficientes para criar o usuário']);
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userController = new UserController();

    $users = $userController->getUsersList();

    if ($users !== false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'users' => $users]);
        exit;
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
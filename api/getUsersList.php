<?php

require_once '../app/models/User.php';
require_once '../app/controllers/UserController.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $currentPage = isset($_GET['currentPage']) ? $_GET['currentPage'] : null;
    $perPage = isset($_GET['usersPerPage']) ? $_GET['usersPerPage'] : null;

    if (!empty($currentPage) && !empty($perPage)) {
        $userController = new UserController();
        $usersList = $userController->getUsersList($currentPage, $perPage);

        if ($usersList != false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $usersList]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Ei, Usuários não encontrados']);
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
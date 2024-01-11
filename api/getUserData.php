<?php
require_once '../app/controllers/UserController.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $userId = isset($_GET['id']) ? $_GET['id'] : null;
    $token = isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : null;

    if (!empty($userId) && !empty($token)) {
        $userController = new UserController();
        $userData = $userController->getUserData($userId, $token);

        if ($userData != false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $userData]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Ei, Usuário não encontrado']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $userId = isset($_GET['id']) ? $_GET['id'] : null;
    $token = isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : null;

    if (isset($requestData['name']) && isset($requestData['password']) && !empty($token) && !empty($userId)) {
        $userController = new UserController();

        $userData = [
            'name' => $requestData['name'],
            'password' => $requestData['password'],
            'user_id' => $userId,
            'token' => $token
        ];

        $updatedUser = $userController->updateUser($userData);

        if ($updatedUser) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso']);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Falha ao atualizar o usuário']);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Dados insuficientes para atualizar o usuário']);
        exit;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método HTTP não suportado']);
}
?>
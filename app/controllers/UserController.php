<?php
require_once '../app/models/User.php';
require_once '../app/models/Database.php';
require_once '../app/controllers/AuthController.php';

class UserController
{
    public function createUser($userData)
    {
        if (!$this->canCreateUser($userData['email'])) {
            echo json_encode(['success' => false, 'message' => 'Usuário já existe']);
            return false;
        } else if (strlen($userData['password']) < 8) {
            echo json_encode(['success' => false, 'message' => 'Senha deve ter 8 caracteres']);
            return false;
        } else if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email inválido']);
            return false;
        } else {
            try {
                $database = new Database();
                $database->getConnection();

                $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

                $query = "INSERT INTO user (name, email, password) VALUES (?, ?, ?)";
                $statement = $database->prepare($query);

                $name = $userData['name'];
                $email = $userData['email'];

                $statement->bind_param('sss', $name, $email, $hashedPassword);

                $statement->execute();
                $statement->close();
                $database->closeConnection();

                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    public function getUsersList()
    {
        try {
            $database = new Database();
            $database->getConnection();

            $query = "SELECT id, name, email FROM user";
            $statement = $database->prepare($query);

            $statement->execute();
            $statement->store_result();

            $users = array();

            if ($statement->num_rows > 0) {
                $statement->bind_result($id, $name, $email);

                while ($statement->fetch()) {
                    array_push($users, array("id" => $id, "name" => $name, "email" => $email));
                }
            }

            $statement->close();
            $database->closeConnection();

            return $users;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUserData($userId, $token)
    {

        $auth = new AuthController();

        $isValidToken = $auth->validateToken($token, $userId);

        if (!$isValidToken) {
            echo json_encode(['success' => false, 'message' => 'Opa! Usuário não autenticado']);
            return false;
        } else {
            try {
                $database = new Database();
                $database->getConnection();

                $queryUser = "SELECT id, name, email FROM user WHERE id = ?";
                $statementUser = $database->prepare($queryUser);
                $statementUser->bind_param('i', $userId);
                $statementUser->execute();
                $statementUser->store_result();

                if ($statementUser->num_rows > 0) {
                    $statementUser->bind_result($id, $name, $email);
                    $statementUser->fetch();

                    $queryDrinkCounter = "SELECT SUM(quantity) AS drinkCounter FROM coffee_consumption WHERE user_id = ?";
                    $statementDrinkCounter = $database->prepare($queryDrinkCounter);
                    $statementDrinkCounter->bind_param('i', $userId);
                    $statementDrinkCounter->execute();
                    $statementDrinkCounter->bind_result($drinkCounter);
                    $statementDrinkCounter->fetch();

                    $userData = [
                        'id' => $id,
                        'name' => $name,
                        'email' => $email,
                        'drinkCounter' => $drinkCounter,
                    ];

                    $statementUser->close();
                    $statementDrinkCounter->close();
                    $database->closeConnection();

                    return $userData;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                    return false;
                }
            } catch (Exception $e) {
                echo $e;
                return false;
            }
        }
    }

    public function updateUser($userData)
    {
        $auth = new AuthController();

        $isValidToken = $auth->validateToken($userData['token'], $userData['user_id']);

        if (!$isValidToken) {
            echo json_encode(['success' => false, 'message' => 'Opa! Usuário não autenticado']);
            return false;
        } else {
            try {
                $database = new Database();
                $database->getConnection();

                $query = "UPDATE user SET name = ?, password = ? WHERE id = ?";
                $statement = $database->prepare($query);

                $name = $userData['name'];
                $password = password_hash($userData['password'], PASSWORD_DEFAULT);
                $userId = $userData['user_id'];

                $statement->bind_param('ssi', $name, $password, $userId);

                $statement->execute();
                $statement->close();
                $database->closeConnection();

                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }


    public function loginUser($email, $password)
    {

        try {
            $database = new Database();
            $database->getConnection();

            $query = "SELECT id, name, email, password FROM user WHERE email = ?";
            $statement = $database->prepare($query);

            $statement->bind_param('s', $email);
            $statement->execute();
            $statement->store_result();

            if ($statement->num_rows > 0) {
                $statement->bind_result($id, $name, $email, $hashedPassword);
                $statement->fetch();

                if (password_verify($password, $hashedPassword)) {
                    $authController = new AuthController();

                    $token = $authController->generateToken($id);
                    return array("id" => $id, "name" => $name, "email" => $email, "token" => $token);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Senha incorreta']);
                    return false;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function logoutUser($id)
    {
        try {
            $database = new Database();
            $database->getConnection();

            $query = "DELETE FROM tokens WHERE user_id = ?";
            $statement = $database->prepare($query);

            $statement->bind_param('i', $id);
            $statement->execute();
            $statement->store_result();

            $isDeleted = $statement->affected_rows > 0;

            $statement->close();
            $database->closeConnection();

            echo json_encode(['success' => $isDeleted, 'message' => 'Usuário deslogado']);
            return $isDeleted;
        } catch (Exception $e) {
            return false;
        }
    }

    private function canCreateUser($email)
    {
        try {
            $database = new Database();
            $database->getConnection();

            $query = "SELECT id FROM user WHERE email = ?";
            $statement = $database->prepare($query);

            $statement->bind_param('s', $email);
            $statement->execute();
            $statement->store_result();

            if ($statement->num_rows > 0) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
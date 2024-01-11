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
                // Usuário encontrado, verificar a senha
                $statement->bind_result($id, $name, $email, $hashedPassword);
                $statement->fetch();

                if (password_verify($password, $hashedPassword)) {
                    // Senha correta, gerar e retornar token
                    $authController = new AuthController();

                    $token = $authController->generateToken($id);
                    return array("id" => $id, "name" => $name, "email" => $email, "token" => $token);
                } else {
                    // Senha incorreta
                    echo json_encode(['success' => false, 'message' => 'Senha incorreta']);
                    return false;
                }
            } else {
                // Usuário não encontrado
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
                // Usuário já existe
                return false;
            } else {
                // Usuário não existe
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
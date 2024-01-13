<?php
require_once '../app/models/User.php';
require_once '../app/models/Database.php';
require_once '../app/controllers/AuthController.php';

/**
 * UserController
 * 
 * Essa classe gerencia as operações relacionadas aos usuários, como criação, autenticação,
 * recuperação de dados, exclusão e outras funcionalidades do sistema de registro de consumo de café.
 * Ela interage com o banco de dados e utiliza métodos da classe AuthController para autenticação
 * e geração de tokens.
 * 
 * Principais operações:
 * - createUser: Cria um novo usuário no sistema.
 * - loginUser: Autentica um usuário existente no sistema.
 * - logoutUser: Realiza o logout de um usuário, invalidando o token.
 * - getUserData: Recupera dados detalhados de um usuário.
 * - getUsersList: Obtém a lista de usuários com suporte para paginação.
 * - updateUser: Atualiza os dados de um usuário autenticado.
 * - deleteUser: Exclui um usuário autenticado, incluindo suas entradas de consumo de café.
 * - getDailyRanking: Retorna o usuário que mais consumiu café em um determinado dia.
 * - getTopCoffeeConsumersLastXDays: Retorna o usuário que mais consumiu café nos últimos X dias.
 *
 */

class UserController
{
    //MÉTODOS PÚBLICOS
    //CRIAÇÃO DE NOVO USUÁRIO
    public function createUser($userData)
    {
        $user = new User();
        $user->setName($userData['name']);
        $user->setEmail($userData['email']);
        $user->setPassword($userData['password']);

        if (!$this->canCreateUser($user)) {
            return false;
        } else {
            try {
                $user = new User();
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

    //MÉTODO PARA ATUALIZAR DADOS DO USUÁRIO COM VALIDAÇÃO DE TOKEN
    public function updateUser($userData, $token)
    {
        $auth = new AuthController();

        $isValidToken = $auth->validateToken($token, $userData['user_id']);

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

    //MÉTODO PARA EXCLUIR USUÁRIO PELO ID E COM VALIDAÇÃO DE TOKEN
    public function deleteUser($userId, $token)
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

                $auth = new AuthController();

                $secureDeletion = $this->secureDeletion($userId, $token);

                if (!$secureDeletion) {
                    echo json_encode(['success' => false, 'message' => 'Falha ao deletar o usuário']);
                    return false;
                } else {

                    $query = "DELETE FROM user WHERE id = ?";
                    $statement = $database->prepare($query);

                    $statement->bind_param('i', $userId);

                    $statement->execute();
                    $statement->close();
                    $database->closeConnection();

                    echo json_encode(['success' => true, 'message' => 'Usuário deletado com sucesso']);
                }

                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    //LISTANDO USUÁRIOS RECEBENDO PÁGINA ATUAL E QUANTO USUÁRIOS POR PÁGINA
    public function getUsersList($currentPage, $usersPerPage)
    {
        try {
            $database = new Database();
            $database->getConnection();

            $offset = ($currentPage - 1) * $usersPerPage;

            $query = "SELECT id, name, email FROM user LIMIT ?, ?";
            $statement = $database->prepare($query);

            $statement->bind_param('ii', $offset, $usersPerPage);

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

    //MÉTODO PARA CONSULTAR DADOS DE USUÁRIO POR ID COM VALIDAÇÃO POR TOKEN
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
                $user = new User();
                $database->getConnection();

                $queryUser = "SELECT id, name, email FROM user WHERE id = ?";
                $statementUser = $database->prepare($queryUser);
                $statementUser->bind_param('i', $userId);
                $statementUser->execute();
                $statementUser->store_result();

                if ($statementUser->num_rows > 0) {
                    $statementUser->bind_result($id, $name, $email);
                    $statementUser->fetch();

                    $user->setEmail($email);
                    $user->setId($id);
                    $user->setName($name);

                    $queryDrinkCounter = "SELECT SUM(quantity) AS drinkCounter FROM coffee_consumption WHERE user_id = ?";
                    $statementDrinkCounter = $database->prepare($queryDrinkCounter);
                    $statementDrinkCounter->bind_param('i', $userId);
                    $statementDrinkCounter->execute();
                    $statementDrinkCounter->bind_result($drinkCounter);
                    $statementDrinkCounter->fetch();

                    $drinkCounter = $drinkCounter ?? 0;
                    $user->setDrinkCounter($drinkCounter);

                    $statementUser->close();
                    $statementDrinkCounter->close();
                    $database->closeConnection();

                    return $user->toArray();
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

    //MÉTODO PARA CONSULTAR USUÁRIO DE MAIOR CONSUMO NO DIA
    public function getDailyRanking($date)
    {
        try {
            $database = new Database();
            $database->getConnection();

            $query = "SELECT u.name AS user_name, SUM(cc.quantity) AS drink_counter
                      FROM user u
                      JOIN coffee_consumption cc ON u.id = cc.user_id
                      WHERE DATE(cc.consumption_date_time) = ?
                      GROUP BY u.id
                      ORDER BY drink_counter DESC
                      LIMIT 1";

            $statement = $database->prepare($query);
            $statement->bind_param("s", $date);
            $statement->execute();
            $statement->bind_result($userName, $drinkCounter);
            $statement->fetch();
            $userData = [
                'name' => $userName,
                'drink_counter' => $drinkCounter
            ];
            $statement->close();
            $database->closeConnection();

            return $userData;

        } catch (Exception $e) {
            echo $e;
            return json_encode(['success' => false, 'message' => 'Erro ao buscar dados']);
        }
    }

    //MÉTODO PARA CONSULTAR USUÁIO COM MAIS CONSUMO EM X DIAS
    public function getTopCoffeeConsumersLastXDays($days)
    {
        try {
            $database = new Database();
            $database->getConnection();

            $query = "SELECT u.name AS user_name, SUM(cc.quantity) AS coffee_count
                  FROM user u
                  JOIN coffee_consumption cc ON u.id = cc.user_id
                  WHERE cc.consumption_date_time >= CURDATE() - INTERVAL ? DAY
                  GROUP BY u.id
                  ORDER BY coffee_count DESC
                  LIMIT 1";

            $statement = $database->prepare($query);
            $statement->bind_param("i", $days);
            $statement->execute();
            $statement->bind_result($userName, $drinkCounter);
            $statement->fetch();

            $userData = [
                "name" => $userName,
                "drink_counter" => $drinkCounter
            ];

            $statement->close();
            $database->closeConnection();

            return $userData;

        } catch (Exception $e) {
            echo $e;
            return json_encode(['success' => false, 'message' => 'Erro ao buscar dados']);
        }
    }

    //MÉTODO PARA FAZER LOGIN DE USUÁRIO
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

                    $user = $this->getUserDataWithoutToken($id);

                    return array_merge($user, array("token" => $token));
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

    //MÉTODO PARA FAZER LOGOUT DO USUÁRIO
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
            echo json_encode(['success' => false, 'message' => 'Erro ao deslogar usuário: ' . $e->getMessage() ]);
            return false;
        }
    }

    //MÉTODOS PRIVADOS
    //MÉTODO QUE VERIFICA SE JÁ EXISTE USUÁRIO COM E-MAIL INFORMADO
    private function canCreateUser(?User $user)
    {
        try {
            $database = new Database();
            $database->getConnection();

            $query = "SELECT id FROM user WHERE email = ?";
            $statement = $database->prepare($query);

            $email = $user->getEmail();

            $statement->bind_param('s', $email);

            $statement->execute();
            $statement->store_result();

            $isEmailAlreadyRegistered = $statement->num_rows > 0;

            $statement->close();
            $database->closeConnection();

            if ($isEmailAlreadyRegistered) {
                echo json_encode(['success' => false, 'message' => 'E-mail já cadastrado']);
                return false;
            } elseif (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'E-mail inválido']);
                return false;
            } elseif (strlen($user->getPassword()) < 8) {
                echo json_encode(['success' => false, 'message' => 'Senha deve ter no mínimo 8 caracteres']);
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    //MÉTODO PARA EXCLUIR TABELAS LIGADAS AO USUÁRIO QUE SERÁ EXCLUÍDO
    private function secureDeletion($userId, $token)
    {
        $auth = new AuthController();

        try {
            $database = new Database();
            $database->getConnection();

            $auth = new AuthController();
            $auth->invalidateToken($token, $userId);

            $query = "DELETE FROM coffee_consumption WHERE user_id = ?";
            $statement = $database->prepare($query);
            $statement->bind_param("i", $userId);
            $statement->execute();
            $statement->close();
            $database->closeConnection();

            return true;
        } catch (Exception $e) {
            return false;
        }

    }

    //MÉTODO PRIVADO PARA CONSULTAR DADOS DE USUÁRIO POR ID SEM VALIDAÇÃO POR TOKEN
    private function getUserDataWithoutToken($id){
        try {
            $database = new Database();
            $user = new User();
            $database->getConnection();

            $queryUser = "SELECT id, name, email FROM user WHERE id = ?";
            $statementUser = $database->prepare($queryUser);
            $statementUser->bind_param('i', $id);
            $statementUser->execute();
            $statementUser->store_result();

            if ($statementUser->num_rows > 0) {
                $statementUser->bind_result($id, $name, $email);
                $statementUser->fetch();

                $user->setEmail($email);
                $user->setId($id);
                $user->setName($name);

                $queryDrinkCounter = "SELECT SUM(quantity) AS drinkCounter FROM coffee_consumption WHERE user_id = ?";
                $statementDrinkCounter = $database->prepare($queryDrinkCounter);
                $statementDrinkCounter->bind_param('i', $id);
                $statementDrinkCounter->execute();
                $statementDrinkCounter->bind_result($drinkCounter);
                $statementDrinkCounter->fetch();

                if ($drinkCounter === null) {
                    $drinkCounter = 0;
                }
                $user->setDrinkCounter($drinkCounter);

                $statementUser->close();
                $statementDrinkCounter->close();
                $database->closeConnection();

                return $user->toArray();
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
?>
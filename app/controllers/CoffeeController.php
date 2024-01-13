<?php
require_once '../app/models/CoffeeConsumption.php';
require_once '../app/models/User.php';
require_once '../app/models/Database.php';
require_once '../app/controllers/AuthController.php';
require_once '../app/controllers/UserController.php';

class CoffeeController
{
    public function createCoffee($coffeeData)
    {
        try {
            $user = new User();
            $userController = new UserController();
            $userJson = $userController->getUserData($coffeeData['user_id'], $coffeeData['token']);

            $user->setId($userJson['id']);
            $user->setName($userJson['name']);
            $user->setEmail($userJson['email']);
            $user->setDrinkCounter(0);

            $coffee = new CoffeeConsumption($user, date('Y-m-d H:i:s'), $coffeeData['drink']);

            $token = isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : null;
            $userId = $coffeeData['user_id'];

            if (!$this->isValidToken($token, $userId)) {
                return json_encode(['success' => false, 'message' => 'Token inválido']);
            }

            $database = new Database();
            $database->getConnection();

            $query = "INSERT INTO coffee_consumption (user_id, consumption_date_time, quantity) VALUES (?, ?, ?)";
            $statement = $database->prepare($query);

            $quantity = $coffee->getDrink();
            $userId = $coffee->getUserId();
            $consumptionDateTime = $coffee->getConsumptionDateTime();

            $statement->bind_param('isi', $userId, $consumptionDateTime, $quantity);

            $statement->execute();
            $statement->close();
            $database->getConnection()->close();

            $userData = new UserController();
            $userData = $userData->getUserData($userId, $token);

            echo json_encode(['success' => true, 'data' => $userData]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    //RETORNAR UM JSON COM O TOTAL DE CAFÉ CONSUMIDO POR DIA
    public function getDailyUserConsumption($userId, $token, $date)
    {
        try {
            if (!$this->isValidToken($token, $userId)) {
                return json_encode(['success' => false, 'message' => 'Token inválido']);
            }

            $database = new Database();
            $database->getConnection();

            $query = "SELECT DATE(consumption_date_time) AS consumption_day, COUNT(*) AS times
                        FROM coffee_consumption
                        WHERE user_id = ? AND DATE(consumption_date_time) = ?
                        GROUP BY DATE(consumption_date_time);";
            $statement = $database->prepare($query);

            $statement->bind_param('is', $userId, $date);

            $statement->execute();
            $result = $statement->get_result();
            $statement->close();

            $database->closeConnection();

            $data = [
                'consumption_day' => $date,
                'times' => 0
            ];

            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();
            }

            return $data;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar dados']);
            return false;
        }
    }


    private function isValidToken($token, $userId)
    {
        $authController = new AuthController();
        return $authController->validateToken($token, $userId);
    }
}

?>
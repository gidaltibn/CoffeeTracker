<?php
require_once '../app/models/CoffeeConsumption.php';
require_once '../app/models/Database.php';
require_once '../app/controllers/AuthController.php';

class CoffeeController
{
    public function createCoffee($coffeeData)
    {
        try {
            $token = isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : null;
            $userId = $coffeeData['user_id'];

            if (!$this->isValidToken($token, $userId)) {
                return json_encode(['success' => false, 'message' => 'Token inválido']);
            }

            $database = new Database();
            $database->getConnection();

            $query = "INSERT INTO coffee_consumption (user_id, consumption_date_time, quantity) VALUES (?, ?, ?)";
            $statement = $database->prepare($query);

            $userId = $coffeeData['user_id'];
            $consumptionDateTime = $coffeeData['consumption_date_time'];
            $quantity = $coffeeData['quantity'];

            $statement->bind_param('isi', $userId, $consumptionDateTime, $quantity);

            $statement->execute();
            $statement->close();
            $query = '';
            $database->getConnection()->close();

            return true;
        } catch (Exception $e) {
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
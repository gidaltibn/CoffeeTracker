<?php
require_once '../app/models/Database.php';

class AuthController
{
    public function generateToken($userId)
    {
        $token = base64_encode(random_bytes(32));

        $database = new Database();
        $database->getConnection();

        $query = "INSERT INTO tokens (user_id, token, created_at) VALUES (?, ?, NOW())";
        $statement = $database->prepare($query);
        $statement->bind_param('is', $userId, $token);
        $statement->execute();
        $statement->close();

        $database->closeConnection();

        return $token;
    }

    public function validateToken($token, $userId) {
        $token = str_replace('Bearer ', '', $token);
        try {
            $database = new Database();
            $database->getConnection();

            $query = "SELECT user_id FROM tokens WHERE token = ? AND user_id = ?";
            $statement = $database->prepare($query);
            $statement->bind_param('ss', $token, $userId);
            $statement->execute();
            $statement->store_result();

            $isValidToken = $statement->num_rows > 0;

            $statement->close();
            $database->closeConnection();

            return $isValidToken;
        } catch (Exception $e) {
            return false;
        }
    }

    public function invalidateToken($token, $userId) {
        $token = str_replace('Bearer ', '', $token);
        try {
            $database = new Database();
            $database->getConnection();

            $query = "DELETE FROM tokens WHERE token = ? AND user_id = ?";
            $statement = $database->prepare($query);
            $statement->bind_param('ss', $token, $userId);
            $statement->execute();
            $statement->store_result();

            $isInvalidatedToken = $statement->affected_rows > 0;

            $statement->close();
            $database->closeConnection();

            return $isInvalidatedToken;
        } catch (Exception $e) {
            return false;
        }
    }

}
?>;
<?php

class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "coffeetracker";

    private $connection;

    public function __construct()
    {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function prepare($query)
    {
        return $this->connection->prepare($query);
    }

    public function closeConnection()
    {
        $this->connection->close();
    }

    public function getError()
    {
        return $this->connection->error;
    }
}
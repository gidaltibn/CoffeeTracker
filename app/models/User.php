<?php

class User
{
    private int $id = 0;
    private string $name = "";
    private string $email = "";
    private string $password = "";
    private int $drinkCounter = 0;

    public function __construct()
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getDrinkCounter(): int
    {
        return $this->drinkCounter;
    }
    public function setDrinkCounter(int $drinkCounter): void
    {
        $this->drinkCounter = $drinkCounter;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->getId(),
            "name" => $this->getName(),
            "email" => $this->getEmail(),
            "drinkCounter" => $this->getDrinkCounter(),
        ];
    }

    public function toJSON(): string
    {
        return json_encode($this->toArray());
    }
}

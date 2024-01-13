<?php

class CoffeeConsumption
{
    private int $id = 0;
    private ?User $user = null;
    private string $consumption_date_time = "";
    private int $drink = 0;

    public function __construct(?User $user = null, string $consumption_date_time = '', int $drink = 0)
    {
        $this->user = $user;
        $this->consumption_date_time = $consumption_date_time;
        $this->drink = $drink;
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getConsumptionDateTime(): string
    {
        return $this->consumption_date_time;
    }

    public function setConsumptionDateTime($consumption_date_time): void
    {
        $this->consumption_date_time = $consumption_date_time;
    }

    public function getDrink(): int
    {
        return $this->drink;
    }

    public function setDrink(int $drink): void
    {
        $this->drink = $drink;
    }

    public function getUserId(): int
    {
        return $this->getUser()->getId();
    }

    public function toArray(): array
    {
        return [
            "id" => $this->getId(),
            "user" => ($this->getUser() !== null) ? $this->getUser()->toArray() : null,
            "consumption_date_time" => $this->getConsumptionDateTime(),
            "drink" => $this->getDrink(),
        ];
    }

    public function toJSON(): string
    {
        return json_encode($this->toArray());
    }
}

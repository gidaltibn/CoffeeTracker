<?php

class CoffeeConsumption
{
    private int $id = 0;
    private ?User $user = null;
    private string $consumption_date_time = "";
    private int $quantity = 0;

    public function __construct(int $id, ?User $user, string $consumption_date_time, int $quantity)
    {
        $this->id = $id;
        $this->user = $user;
        $this->consumption_date_time = $consumption_date_time;
        $this->quantity = $quantity;
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

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->getId(),
            "user" => ($this->getUser() !== null) ? $this->getUser()->toArray() : null,
            "consumption_date_time" => $this->getConsumptionDateTime(),
            "quantity" => $this->getQuantity(),
        ];
    }

    public function toJSON(): string
    {
        return json_encode($this->toArray());
    }
}

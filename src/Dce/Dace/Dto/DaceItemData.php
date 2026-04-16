<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Dto;

final class DaceItemData
{
    public function __construct(
        private readonly int $itemNumber,
        private readonly string $description,
        private readonly string $quantity,
        private readonly string $amountFormatted,
    ) {
    }

    public function getItemNumber(): int
    {
        return $this->itemNumber;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getQuantity(): string
    {
        return $this->quantity;
    }

    public function getAmountFormatted(): string
    {
        return $this->amountFormatted;
    }
}

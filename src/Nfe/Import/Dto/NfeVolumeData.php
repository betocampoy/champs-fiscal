<?php

namespace BetoCampoy\Champs\Fiscal\Nfe\Import\Dto;

final class NfeVolumeData
{
    public function __construct(
        public ?int $quantity,
        public ?string $species,
        public ?string $brand,
        public ?string $numbering,
        public ?int $grossWeightGrams,
        public ?int $netWeightGrams,
    ) {
    }
}

<?php

namespace BetoCampoy\Champs\Fiscal\Nfe\Import\Dto;

final class NfePartyData
{
    public function __construct(
        public ?string $document,
        public ?string $name,
        public ?string $phone = null,
        public ?string $email = null,
    ) {
    }
}

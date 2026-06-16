<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Request\Input;

final class NfseTakerAddressRequest
{
    public function __construct(
        public readonly string $ibgeCode,
        public readonly string $zipCode,
        public readonly ?string $street = null,
        public readonly ?string $number = null,
        public readonly ?string $complement = null,
        public readonly ?string $neighborhood = null,
    ) {
    }
}

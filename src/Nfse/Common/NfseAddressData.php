<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Common;

final class NfseAddressData
{
    public function __construct(
        public readonly string $ibgeCode,
        public readonly string $zipCode,
        public readonly ?string $street = null,
        public readonly ?string $number = null,
        public readonly ?string $complement = null,
        public readonly ?string $neighborhood = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $countryCode = null,
    ) {
    }
}

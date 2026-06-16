<?php

namespace BetoCampoy\Champs\Fiscal\Nfe\Import\Dto;

final class NfeAddressData
{
    public function __construct(
        public ?string $street,
        public ?string $number,
        public ?string $complement,
        public ?string $neighborhood,
        public ?string $cityCode,
        public ?string $city,
        public ?string $state,
        public ?string $zipcode,
        public ?string $countryCode,
        public ?string $country,
    ) {
    }
}

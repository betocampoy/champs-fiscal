<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Common;

final class DceAddress
{
    public function __construct(
        public readonly string $street,
        public readonly string $number,
        public readonly ?string $complement,
        public readonly string $district,
        public readonly string $cityCode,
        public readonly string $cityName,
        public readonly string $uf,
        public readonly ?string $zipCode,
        public readonly string $countryCode,
        public readonly string $countryName,
        public readonly ?string $phone,
    ) {
    }
}

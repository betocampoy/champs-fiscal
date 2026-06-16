<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Request\Input;

final class NfseServiceRequest
{
    public function __construct(
        public readonly string $nationalServiceCode,
        public readonly string $municipalServiceCode,
        public readonly string $description,
        public readonly string $serviceMunicipalityIbge,
        public readonly ?string $cnae = null,
    ) {
    }
}

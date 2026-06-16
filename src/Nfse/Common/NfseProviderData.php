<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Common;

final class NfseProviderData
{
    public function __construct(
        public readonly ?string $cnpj,
        public readonly ?string $cpf,
        public readonly string $municipalRegistration,
        public readonly string $emitterIbgeCode,
        public readonly bool $simplesNacional = false,
    ) {
        if ($this->cnpj === null && $this->cpf === null) {
            throw new \InvalidArgumentException('Prestador deve ter CNPJ ou CPF.');
        }
    }
}

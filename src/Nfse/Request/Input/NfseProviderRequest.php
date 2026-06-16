<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Request\Input;

final class NfseProviderRequest
{
    public function __construct(
        public readonly string $municipalRegistration,
        public readonly string $emitterIbgeCode,
        public readonly ?string $cnpj = null,
        public readonly ?string $cpf = null,
        public readonly bool $simplesNacional = false,
    ) {
        if ($this->cnpj === null && $this->cpf === null) {
            throw new \InvalidArgumentException('Prestador deve informar CNPJ ou CPF.');
        }

        if (trim($this->municipalRegistration) === '') {
            throw new \InvalidArgumentException('Inscrição Municipal do prestador é obrigatória para NFS-e Nacional.');
        }
    }
}

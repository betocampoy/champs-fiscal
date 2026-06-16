<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Request\Input;

final class NfseTakerRequest
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $cnpj = null,
        public readonly ?string $cpf = null,
        public readonly ?string $foreignId = null,
        public readonly ?NfseTakerAddressRequest $address = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
    ) {
        if ($this->cnpj === null && $this->cpf === null && $this->foreignId === null) {
            throw new \InvalidArgumentException('Tomador deve informar CNPJ, CPF ou identificador estrangeiro.');
        }
    }
}

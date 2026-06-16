<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Common;

final class NfseTakerData
{
    public function __construct(
        public readonly ?string $cnpj,
        public readonly ?string $cpf,
        public readonly ?string $foreignId,
        public readonly string $name,
        public readonly ?NfseAddressData $address = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
    ) {
        if ($this->cnpj === null && $this->cpf === null && $this->foreignId === null) {
            throw new \InvalidArgumentException('Tomador deve ter CNPJ, CPF ou identificador estrangeiro.');
        }
    }
}

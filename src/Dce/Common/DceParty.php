<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Common;

final class DceParty
{
    public function __construct(
        public readonly ?string $cnpj,
        public readonly ?string $cpf,
        public readonly ?string $idOutros,
        public readonly string $name,
        public readonly DceAddress $address,
        public readonly ?string $email = null,
        public readonly ?string $site = null,
        public readonly ?string $uf = null,
    ) {
    }
}

<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Authorization;

final class DceAuthorizationItem
{
    public function __construct(
        public readonly string $xProd,
        public readonly ?string $ncm,
        public readonly float $qCom,
        public readonly float $vUnCom,
        public readonly float $vProd,
        public readonly ?string $infAdProd = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->qCom <= 0) {
            throw new \InvalidArgumentException('Quantidade deve ser maior que zero.');
        }

        if ($this->vUnCom < 0) {
            throw new \InvalidArgumentException('Valor unitário inválido.');
        }

        if ($this->vProd < 0) {
            throw new \InvalidArgumentException('Valor do produto inválido.');
        }

        if (trim($this->xProd) === '') {
            throw new \InvalidArgumentException('Descrição do produto é obrigatória.');
        }
    }
}

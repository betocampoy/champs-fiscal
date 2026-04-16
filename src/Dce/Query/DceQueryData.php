<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Query;

final class DceQueryData
{
    public function __construct(
        public readonly string $versao,
        public readonly int $tpAmb,
        public readonly string $xServ,
        public readonly string $chDCe,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (trim($this->versao) === '') {
            throw new \InvalidArgumentException('Versão da consulta é obrigatória.');
        }

        if (!in_array($this->tpAmb, [1, 2], true)) {
            throw new \InvalidArgumentException('tpAmb inválido. Valores aceitos: 1 ou 2.');
        }

        if (trim($this->xServ) === '') {
            throw new \InvalidArgumentException('xServ é obrigatório.');
        }

        if (!preg_match('/^\d{44}$/', $this->chDCe)) {
            throw new \InvalidArgumentException('chDCe deve conter exatamente 44 dígitos.');
        }
    }
}

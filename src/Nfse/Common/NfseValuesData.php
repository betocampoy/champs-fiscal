<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Common;

final class NfseValuesData
{
    public function __construct(
        public readonly float $serviceValue,
        public readonly float $issAliquot,
        public readonly string $issIncidenceIbge,
        public readonly bool $issRetained = false,
        public readonly float $unconditionalDiscount = 0.0,
        public readonly float $conditionalDiscount = 0.0,
        public readonly ?float $pisValue = null,
        public readonly ?float $cofinsValue = null,
        public readonly ?float $inssValue = null,
        public readonly ?float $irValue = null,
        public readonly ?float $csllValue = null,
    ) {
        if ($this->serviceValue <= 0) {
            throw new \InvalidArgumentException('Valor do serviço deve ser maior que zero.');
        }

        if ($this->issAliquot < 0 || $this->issAliquot > 1) {
            throw new \InvalidArgumentException('Alíquota ISS deve ser entre 0 e 1 (ex: 0.05 para 5%).');
        }
    }

    public function getIssValue(): float
    {
        $base = $this->serviceValue - $this->unconditionalDiscount;
        return round($base * $this->issAliquot, 2);
    }

    public function getNetValue(): float
    {
        $issDeducted = $this->issRetained ? $this->getIssValue() : 0.0;
        $deductions = $this->unconditionalDiscount + $this->conditionalDiscount + $issDeducted;
        return round($this->serviceValue - $deductions, 2);
    }
}

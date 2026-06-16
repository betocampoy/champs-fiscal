<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Request\Input;

final class NfseValuesRequest
{
    public function __construct(
        public readonly float $serviceValue,
        public readonly float $issAliquot,
        public readonly bool $issRetained = false,
        public readonly float $unconditionalDiscount = 0.0,
        public readonly float $conditionalDiscount = 0.0,
        public readonly ?float $pisValue = null,
        public readonly ?float $cofinsValue = null,
        public readonly ?float $inssValue = null,
        public readonly ?float $irValue = null,
        public readonly ?float $csllValue = null,
    ) {
    }
}

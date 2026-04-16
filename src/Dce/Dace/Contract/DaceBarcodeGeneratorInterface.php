<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Contract;

interface DaceBarcodeGeneratorInterface
{
    public function generateBase64(string $content): string;
}

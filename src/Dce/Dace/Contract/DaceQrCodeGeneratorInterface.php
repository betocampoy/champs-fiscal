<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Contract;

interface DaceQrCodeGeneratorInterface
{
    public function generateBase64(string $content): string;
}

<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Import\Contract;

interface DocumentTypeDetectorInterface
{
    public function detect(string $xmlContent): ?string;
}

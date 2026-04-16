<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Common;

final class DceDocumentItem
{
    public function __construct(
        public readonly int $itemNumber,
        public readonly string $description,
        public readonly ?string $ncm,
        public readonly string $quantity,
        public readonly string $unitValue,
        public readonly string $totalValue,
        public readonly ?string $additionalInfo = null,
    ) {
    }
}

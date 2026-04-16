<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

/**
 * Grupo total da DC-e.
 *
 * XSD:
 * infDCe/total
 */
final class DceTotalRequest
{
    /**
     * vDC
     * Valor total da DC-e.
     */
    private ?string $totalValue = null;

    public function getTotalValue(): ?string
    {
        return $this->totalValue;
    }

    public function setTotalValue(?string $totalValue): self
    {
        $this->totalValue = $totalValue;
        return $this;
    }
}

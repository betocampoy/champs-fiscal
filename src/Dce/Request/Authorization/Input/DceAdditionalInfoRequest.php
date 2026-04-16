<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

/**
 * Grupo infAdic da DC-e.
 *
 * Informações adicionais do documento.
 */
final class DceAdditionalInfoRequest
{
    /**
     * infCpl
     * Informações complementares.
     */
    private ?string $complementaryInfo = null;

    public function getComplementaryInfo(): ?string
    {
        return $this->complementaryInfo;
    }

    public function setComplementaryInfo(?string $complementaryInfo): self
    {
        $this->complementaryInfo = $complementaryInfo;
        return $this;
    }
}

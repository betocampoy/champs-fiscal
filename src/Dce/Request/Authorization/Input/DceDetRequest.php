<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

/**
 * Grupo det da DC-e.
 *
 * XSD:
 * infDCe/det
 */
final class DceDetRequest
{
    /**
     * nItem
     * Número sequencial do item na DC-e.
     */
    private ?string $itemNumber = null;

    /**
     * prod
     * Dados do produto ou serviço.
     */
    private ?DceProdRequest $prod = null;

    /**
     * infAdProd
     * Informações adicionais do item.
     */
    private ?string $additionalInfo = null;

    public function getItemNumber(): ?string
    {
        return $this->itemNumber;
    }

    public function setItemNumber(?string $itemNumber): self
    {
        $this->itemNumber = $itemNumber;
        return $this;
    }

    public function getProd(): ?DceProdRequest
    {
        return $this->prod;
    }

    public function setProd(?DceProdRequest $prod): self
    {
        $this->prod = $prod;
        return $this;
    }

    public function getAdditionalInfo(): ?string
    {
        return $this->additionalInfo;
    }

    public function setAdditionalInfo(?string $additionalInfo): self
    {
        $this->additionalInfo = $additionalInfo;
        return $this;
    }
}

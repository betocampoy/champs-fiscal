<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

/**
 * Grupo prod da DC-e.
 *
 * XSD:
 * det/prod
 */
final class DceProdRequest
{
    /**
     * xProd
     * Descrição do produto, bem ou mercadoria.
     */
    private ?string $name = null;

    /**
     * NCM
     * Código NCM.
     * Aceita capítulo com 2 dígitos ou NCM completo com 8 dígitos.
     */
    private ?string $ncm = null;

    /**
     * qCom
     * Quantidade comercial.
     */
    private ?string $commercialQuantity = null;

    /**
     * vUnCom
     * Valor unitário comercial.
     */
    private ?string $unitValue = null;

    /**
     * vProd
     * Valor total bruto do item.
     */
    private ?string $totalValue = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getNcm(): ?string
    {
        return $this->ncm;
    }

    public function setNcm(?string $ncm): self
    {
        $this->ncm = $ncm;
        return $this;
    }

    public function getCommercialQuantity(): ?string
    {
        return $this->commercialQuantity;
    }

    public function setCommercialQuantity(?string $commercialQuantity): self
    {
        $this->commercialQuantity = $commercialQuantity;
        return $this;
    }

    public function getUnitValue(): ?string
    {
        return $this->unitValue;
    }

    public function setUnitValue(?string $unitValue): self
    {
        $this->unitValue = $unitValue;
        return $this;
    }

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

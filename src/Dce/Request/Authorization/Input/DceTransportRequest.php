<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

/**
 * Grupo transp da DC-e.
 *
 * Representa os dados do transporte da operação.
 *
 * NÃO confundir com DceTranspRequest (transportadora emissora).
 */
final class DceTransportRequest
{
    /**
     * modFrete
     * Modalidade do frete.
     *
     * Valores comuns:
     * 0 = por conta do emitente
     * 1 = por conta do destinatário
     * 9 = sem frete
     */
    private ?string $freightMode = null;

    /**
     * vFrete
     * Valor do frete.
     */
    private ?string $freightValue = null;

    private ?string $carrierCnpj = null;

    public function getCarrierCnpj(): ?string
    {
        return $this->carrierCnpj;
    }

    public function setCarrierCnpj(?string $carrierCnpj): self
    {
        $this->carrierCnpj = $carrierCnpj;
        return $this;
    }

    public function getFreightMode(): ?string
    {
        return $this->freightMode;
    }

    public function setFreightMode(?string $freightMode): self
    {
        $this->freightMode = $freightMode;
        return $this;
    }

    public function getFreightValue(): ?string
    {
        return $this->freightValue;
    }

    public function setFreightValue(?string $freightValue): self
    {
        $this->freightValue = $freightValue;
        return $this;
    }
}

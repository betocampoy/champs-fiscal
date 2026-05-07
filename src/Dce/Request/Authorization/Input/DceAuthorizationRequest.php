<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

/**
 * Request principal da autorização da DC-e.
 *
 * Representa a estrutura semântica do documento de autorização
 * antes da transformação para o payload técnico/XML.
 *
 * Esta classe:
 * - não depende de HTTP
 * - não depende do Minha Encomenda
 * - não conhece XML
 * - apenas organiza os dados da operação
 */
final class DceAuthorizationRequest
{
    /**
     * Grupo ide
     * Identificação da DC-e.
     */
    private ?DceIdeRequest $ide = null;

    /**
     * Grupo emit
     * Emitente da DC-e.
     */
    private ?DceEmitRequest $emit = null;

    /**
     * Grupo Transportadora
     * Utilizado quando tpEmit = 3.
     *
     * Atenção:
     * este grupo representa a transportadora emissora da DC-e,
     * e não os dados de frete da operação.
     */
    private ?DceTranspRequest $transp = null;

    private ?DceMarketplaceRequest $marketplace = null;
    private ?DceEmpEmisPropRequest $empEmisProp = null;

    /**
     * Grupo dest
     * Destinatário da DC-e.
     */
    private ?DceDestRequest $dest = null;

    /**
     * Grupo autXML
     * Pessoas autorizadas a acessar o XML da DC-e.
     *
     * O XSD permite até 10 ocorrências.
     *
     * @var DceAutXmlRequest[]
     */
    private array $authorizedXmlViewers = [];

    /**
     * Grupo det
     * Itens da DC-e.
     *
     * @var DceDetRequest[]
     */
    private array $details = [];

    /**
     * Grupo total
     * Totais da DC-e.
     */
    private ?DceTotalRequest $total = null;

    /**
     * Grupo transp
     * Dados de transporte/frete da operação.
     *
     * Atenção:
     * não confundir com $transp, que representa a transportadora emissora.
     */
    private ?DceTransportRequest $transport = null;

    /**
     * Grupo infAdic
     * Informações adicionais da DC-e.
     */
    private ?DceAdditionalInfoRequest $additionalInfo = null;

    public function getIde(): ?DceIdeRequest
    {
        return $this->ide;
    }

    public function setIde(?DceIdeRequest $ide): self
    {
        $this->ide = $ide;
        return $this;
    }

    public function getEmit(): ?DceEmitRequest
    {
        return $this->emit;
    }

    public function setEmit(?DceEmitRequest $emit): self
    {
        $this->emit = $emit;
        return $this;
    }

    public function getTransp(): ?DceTranspRequest
    {
        return $this->transp;
    }

    public function setTransp(?DceTranspRequest $transp): self
    {
        $this->transp = $transp;
        return $this;
    }

    public function getMarketplace(): ?DceMarketplaceRequest
    {
        return $this->marketplace;
    }

    public function setMarketplace(?DceMarketplaceRequest $marketplace): self
    {
        $this->marketplace = $marketplace;
        return $this;
    }

    public function getEmpEmisProp(): ?DceEmpEmisPropRequest
    {
        return $this->empEmisProp;
    }

    public function setEmpEmisProp(?DceEmpEmisPropRequest $empEmisProp): self
    {
        $this->empEmisProp = $empEmisProp;
        return $this;
    }

    public function getDest(): ?DceDestRequest
    {
        return $this->dest;
    }

    public function setDest(?DceDestRequest $dest): self
    {
        $this->dest = $dest;
        return $this;
    }

    /**
     * @return DceAutXmlRequest[]
     */
    public function getAuthorizedXmlViewers(): array
    {
        return $this->authorizedXmlViewers;
    }

    /**
     * @param DceAutXmlRequest[] $authorizedXmlViewers
     */
    public function setAuthorizedXmlViewers(array $authorizedXmlViewers): self
    {
        $this->authorizedXmlViewers = $authorizedXmlViewers;
        return $this;
    }

    public function addAuthorizedXmlViewer(DceAutXmlRequest $authorizedXmlViewer): self
    {
        $this->authorizedXmlViewers[] = $authorizedXmlViewer;
        return $this;
    }

    /**
     * @return DceDetRequest[]
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param DceDetRequest[] $details
     */
    public function setDetails(array $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function addDetail(DceDetRequest $detail): self
    {
        $this->details[] = $detail;
        return $this;
    }

    public function getTotal(): ?DceTotalRequest
    {
        return $this->total;
    }

    public function setTotal(?DceTotalRequest $total): self
    {
        $this->total = $total;
        return $this;
    }

    public function getTransport(): ?DceTransportRequest
    {
        return $this->transport;
    }

    public function setTransport(?DceTransportRequest $transport): self
    {
        $this->transport = $transport;
        return $this;
    }

    public function getAdditionalInfo(): ?DceAdditionalInfoRequest
    {
        return $this->additionalInfo;
    }

    public function setAdditionalInfo(?DceAdditionalInfoRequest $additionalInfo): self
    {
        $this->additionalInfo = $additionalInfo;
        return $this;
    }
}

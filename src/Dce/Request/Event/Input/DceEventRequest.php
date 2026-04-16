<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Event\Input;

final class DceEventRequest
{
    private ?string $accessKey = null;
    private ?string $environment = null;
    private ?string $version = null;
    private ?string $service = null;
    private ?string $eventVersion = null;
    private ?string $eventType = null;
    private ?string $sequence = null;
    private ?string $eventDate = null;
    private ?string $authorDocument = null;
    private ?string $justification = null;
    private ?string $cOrgao = null;
    private ?string $tpEmit = null;
    private ?string $protocolNumber = null;
    private ?string $eventDescription = null;
    private ?string $issuerCnpj = null;
    private ?string $issuerCpf = null;
    private ?string $issuerOtherId = null;

    public function getAccessKey(): ?string
    {
        return $this->accessKey;
    }

    public function setAccessKey(?string $accessKey): self
    {
        $this->accessKey = $accessKey;
        return $this;
    }

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    public function setEnvironment(?string $environment): self
    {
        $this->environment = $environment;
        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getEventVersion(): ?string
    {
        return $this->eventVersion;
    }

    public function setEventVersion(?string $eventVersion): self
    {
        $this->eventVersion = $eventVersion;
        return $this;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(?string $eventType): self
    {
        $this->eventType = $eventType;
        return $this;
    }

    public function getSequence(): ?string
    {
        return $this->sequence;
    }

    public function setSequence(?string $sequence): self
    {
        $this->sequence = $sequence;
        return $this;
    }

    public function getEventDate(): ?string
    {
        return $this->eventDate;
    }

    public function setEventDate(?string $eventDate): self
    {
        $this->eventDate = $eventDate;
        return $this;
    }

    public function getAuthorDocument(): ?string
    {
        return $this->authorDocument;
    }

    public function setAuthorDocument(?string $authorDocument): self
    {
        $this->authorDocument = $authorDocument;
        return $this;
    }

    public function getJustification(): ?string
    {
        return $this->justification;
    }

    public function setJustification(?string $justification): self
    {
        $this->justification = $justification;
        return $this;
    }

    public function getCOrgao(): ?string
    {
        return $this->cOrgao;
    }

    public function setCOrgao(?string $cOrgao): self
    {
        $this->cOrgao = $cOrgao;
        return $this;
    }

    public function getTpEmit(): ?string
    {
        return $this->tpEmit;
    }

    public function setTpEmit(?string $tpEmit): self
    {
        $this->tpEmit = $tpEmit;
        return $this;
    }

    public function getProtocolNumber(): ?string
    {
        return $this->protocolNumber;
    }

    public function setProtocolNumber(?string $protocolNumber): self
    {
        $this->protocolNumber = $protocolNumber;
        return $this;
    }

    public function getEventDescription(): ?string
    {
        return $this->eventDescription;
    }

    public function setEventDescription(?string $eventDescription): self
    {
        $this->eventDescription = $eventDescription;
        return $this;
    }

    public function getIssuerCnpj(): ?string
    {
        return $this->issuerCnpj;
    }

    public function setIssuerCnpj(?string $issuerCnpj): self
    {
        $this->issuerCnpj = $issuerCnpj;
        return $this;
    }

    public function getIssuerCpf(): ?string
    {
        return $this->issuerCpf;
    }

    public function setIssuerCpf(?string $issuerCpf): self
    {
        $this->issuerCpf = $issuerCpf;
        return $this;
    }

    public function getIssuerOtherId(): ?string
    {
        return $this->issuerOtherId;
    }

    public function setIssuerOtherId(?string $issuerOtherId): self
    {
        $this->issuerOtherId = $issuerOtherId;
        return $this;
    }
}

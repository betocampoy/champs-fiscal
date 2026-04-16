<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

final class DceIdeRequest
{
    /**
     * UF de emissão/autorização da DC-e.
     * Ex.: PR, SP, RJ
     */
    private ?string $state = null;
    private ?string $ufCode = null;
    private ?string $randomCode = null;
    private ?string $model = null;
    private ?string $series = null;
    private ?string $number = null;
    private ?\DateTimeImmutable $issuedAt = null;
    private ?string $emissionType = null;
    private ?string $issuerType = null;
    private ?string $authorizerSiteNumber = null;
    private ?string $checkDigit = null;
    private ?string $environment = null;
    private ?string $processVersion = null;

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getUfCode(): ?string
    {
        return $this->ufCode;
    }

    public function setUfCode(?string $ufCode): self
    {
        $this->ufCode = $ufCode;
        return $this;
    }

    public function getRandomCode(): ?string
    {
        return $this->randomCode;
    }

    public function setRandomCode(?string $randomCode): self
    {
        $this->randomCode = $randomCode;
        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getSeries(): ?string
    {
        return $this->series;
    }

    public function setSeries(?string $series): self
    {
        $this->series = $series;
        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;
        return $this;
    }

    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(?\DateTimeImmutable $issuedAt): self
    {
        $this->issuedAt = $issuedAt;
        return $this;
    }

    public function getEmissionType(): ?string
    {
        return $this->emissionType;
    }

    public function setEmissionType(?string $emissionType): self
    {
        $this->emissionType = $emissionType;
        return $this;
    }

    public function getIssuerType(): ?string
    {
        return $this->issuerType;
    }

    public function setIssuerType(?string $issuerType): self
    {
        $this->issuerType = $issuerType;
        return $this;
    }

    public function getAuthorizerSiteNumber(): ?string
    {
        return $this->authorizerSiteNumber;
    }

    public function setAuthorizerSiteNumber(?string $authorizerSiteNumber): self
    {
        $this->authorizerSiteNumber = $authorizerSiteNumber;
        return $this;
    }

    public function getCheckDigit(): ?string
    {
        return $this->checkDigit;
    }

    public function setCheckDigit(?string $checkDigit): self
    {
        $this->checkDigit = $checkDigit;
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

    public function getProcessVersion(): ?string
    {
        return $this->processVersion;
    }

    public function setProcessVersion(?string $processVersion): self
    {
        $this->processVersion = $processVersion;
        return $this;
    }
}

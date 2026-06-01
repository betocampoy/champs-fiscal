<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

/**
 * Endereço do emitente.
 *
 * XSD:
 * TEndeEmi
 */
final class DceEmitAddressRequest
{
    /**
     * xLgr
     * Logradouro.
     */
    private ?string $street = null;

    /**
     * nro
     * Número.
     */
    private ?string $number = null;

    /**
     * xCpl
     * Complemento.
     */
    private ?string $complement = null;

    /**
     * xBairro
     * Bairro.
     */
    private ?string $neighborhood = null;

    /**
     * cMun
     * Código do município no IBGE.
     */
    private ?string $cityCode = null;

    /**
     * xMun
     * Nome do município.
     */
    private ?string $cityName = null;

    /**
     * UF
     * Sigla da UF.
     */
    private ?string $state = null;

    /**
     * CEP
     * CEP com 8 dígitos.
     */
    private ?string $zipCode = null;

    /**
     * cPais
     * Código do país.
     * No XSD: 1058.
     */
    private ?string $countryCode = null;

    /**
     * xPais
     * Nome do país.
     * No XSD: Brasil ou BRASIL.
     */
    private ?string $countryName = null;

    /**
     * fone
     * Telefone.
     */
    private ?string $phone = null;

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;
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

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function setComplement(?string $complement): self
    {
        $this->complement = $complement;
        return $this;
    }

    public function getNeighborhood(): ?string
    {
        return $this->neighborhood;
    }

    public function setNeighborhood(?string $neighborhood): self
    {
        $this->neighborhood = $neighborhood;
        return $this;
    }

    public function getCityCode(): ?string
    {
        return $this->cityCode;
    }

    public function setCityCode(?string $cityCode): self
    {
        $this->cityCode = $cityCode;
        return $this;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): self
    {
        $this->cityName = $cityName;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getZipCode(): ?string
    {
        return str_pad($this->zipCode, 8, '0', STR_PAD_LEFT);
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function setCountryName(?string $countryName): self
    {
        $this->countryName = $countryName;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }
}

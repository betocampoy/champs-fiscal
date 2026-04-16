<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Dto;

final class DacePartyData
{
    public function __construct(
        private readonly ?string $document,      // CNPJ/CPF já formatado
        private readonly string $name,           // Nome ou razão social
        private readonly string $cityUf,         // Ex: "São Paulo - SP"
        private readonly string $fullAddress     // Endereço completo já montado
    ) {
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function hasDocument(): bool
    {
        return $this->document !== null && $this->document !== '';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCityUf(): string
    {
        return $this->cityUf;
    }

    public function getFullAddress(): string
    {
        return $this->fullAddress;
    }
}

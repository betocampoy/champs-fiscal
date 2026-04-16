<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Dto;

final class DaceAdditionalData
{
    public function __construct(
        private readonly ?string $complementaryInformation,
        private readonly ?string $taxInformation,
        private readonly ?string $observations,
        private readonly ?string $legalText,
    ) {
    }

    public function getComplementaryInformation(): ?string
    {
        return $this->complementaryInformation;
    }

    public function hasComplementaryInformation(): bool
    {
        return $this->complementaryInformation !== null && $this->complementaryInformation !== '';
    }

    public function getTaxInformation(): ?string
    {
        return $this->taxInformation;
    }

    public function hasTaxInformation(): bool
    {
        return $this->taxInformation !== null && $this->taxInformation !== '';
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function hasObservations(): bool
    {
        return $this->observations !== null && $this->observations !== '';
    }

    public function getLegalText(): ?string
    {
        return $this->legalText;
    }

    public function hasLegalText(): bool
    {
        return $this->legalText !== null && $this->legalText !== '';
    }
}

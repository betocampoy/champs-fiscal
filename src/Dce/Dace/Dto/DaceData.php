<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Dto;

final class DaceData
{
    /**
     * @param DaceItemData[] $items
     */
    public function __construct(
        private readonly DaceDocumentData $document,
        private readonly DacePartyData $issuer,
        private readonly DacePartyData $recipient,
        private readonly ?DacePartyData $responsibleParty,
        private readonly array $items,
        private readonly DaceAdditionalData $additional,
        private readonly DaceVisualAssetsData $assets,
    ) {
    }

    public function getDocument(): DaceDocumentData
    {
        return $this->document;
    }

    public function getIssuer(): DacePartyData
    {
        return $this->issuer;
    }

    public function getRecipient(): DacePartyData
    {
        return $this->recipient;
    }

    public function getResponsibleParty(): ?DacePartyData
    {
        return $this->responsibleParty;
    }

    /**
     * @return DaceItemData[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getAdditional(): DaceAdditionalData
    {
        return $this->additional;
    }

    public function getAssets(): DaceVisualAssetsData
    {
        return $this->assets;
    }

    public function hasResponsibleParty(): bool
    {
        return $this->responsibleParty !== null;
    }

    public function hasItems(): bool
    {
        return $this->items !== [];
    }
}

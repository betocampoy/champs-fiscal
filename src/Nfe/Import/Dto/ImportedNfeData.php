<?php

namespace BetoCampoy\Champs\Fiscal\Nfe\Import\Dto;

final class ImportedNfeData
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<int, NfeVolumeData> $volumes
     * @param array<string, mixed> $details
     */
    public function __construct(
        public string $accessKey,
        public ?string $environment,
        public ?string $state,
        public ?string $stateCode,
        public string $series,
        public string $number,
        public string $issuedAt,

        public NfePartyData $emitter,
        public ?NfeAddressData $emitterAddress,

        public NfePartyData $recipient,

        /**
         * Endereço principal/fiscal da NF-e.
         */
        public NfeAddressData $billingAddress,

        /**
         * Endereço operacional da entrega.
         * Se a NF-e não tiver entrega, vem igual ao billingAddress.
         */
        public NfeAddressData $deliveryAddress,

        public ?NfePartyData $transporter,

        public float $totalValue,
        public ?string $freightMode,
        public ?float $freightValue,

        public int $packageCount,
        public int $totalWeightGrams,

        public array $items,
        public array $volumes,
        public array $details,
        public ?string $additionalInfo,
    ) {
    }
}

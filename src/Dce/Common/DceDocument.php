<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Common;

final class DceDocument
{
    /**
     * @param DceDocumentItem[] $items
     * @param array<int, array{type:string, document:string}> $authorizedXml
     */
    public function __construct(
        public readonly string $accessKey,
        public readonly string $version,
        public readonly string $number,
        public readonly string $series,
        public readonly string $model,
        public readonly string $ufCode,
        public readonly \DateTimeImmutable $issuedAt,
        public readonly int $environment,
        public readonly int $emissionType,
        public readonly int $issuerType,
        public readonly int $authorizerSite,
        public readonly string $numericCode,
        public readonly string $checkDigit,
        public readonly string $processVersion,

        public readonly DceParty $issuer,
        public readonly ?DceParty $fiscoIssuer,
        public readonly ?DceParty $marketplaceIssuer,
        public readonly ?DceParty $transportIssuer,
        public readonly ?DceParty $selfIssuer,
        public readonly DceParty $recipient,

        public readonly array $authorizedXml,
        public readonly array $items,
        public readonly string $totalValue,

        public readonly int $transportMode,
        public readonly ?string $transportCompanyCnpj,

        public readonly ?string $additionalTaxInfo,
        public readonly ?string $additionalUserInfo,
        public readonly ?string $additionalMarketplaceInfo,
        public readonly ?string $additionalTransportInfo,

        public readonly string $legalObservation1,
        public readonly string $legalObservation2,

        public readonly ?string $fiscoRequestJson,

        public readonly string $qrCode,
        public readonly string $queryUrl,
        public readonly ?string $rawXml = null,
    ) {
    }
}

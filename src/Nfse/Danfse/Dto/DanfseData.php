<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Danfse\Dto;

/**
 * Dados do DANFSe (Documento Auxiliar da NFS-e Nacional) extraídos do XML autorizado.
 *
 * Textos já vêm formatados; valores monetários e percentuais vêm como float|null
 * (null = campo ausente no XML, exibido como "-").
 */
final class DanfseData
{
    public function __construct(
        // Identificação
        public readonly string $accessKey,
        public readonly bool $production,
        public readonly ?string $nfseNumber,
        public readonly ?string $competence,            // d/m/Y
        public readonly ?string $issuedAt,              // d/m/Y H:i:s (processamento da NFS-e)
        public readonly ?string $dpsNumber,
        public readonly ?string $dpsSeries,
        public readonly ?string $dpsIssuedAt,           // d/m/Y H:i:s
        public readonly ?string $issuingCity,           // município emissor

        // Partes
        public readonly DanfsePartyData $provider,
        public readonly ?DanfsePartyData $taker,
        public readonly ?DanfsePartyData $intermediary,

        // Serviço
        public readonly ?string $nationalTaxCode,       // 01.03.02 - descrição
        public readonly ?string $municipalTaxCode,
        public readonly ?string $serviceLocation,
        public readonly ?string $serviceCountry,
        public readonly ?string $serviceDescription,

        // Tributação municipal
        public readonly ?string $issqnTaxation,
        public readonly ?string $resultCountry,
        public readonly ?string $issqnIncidenceCity,
        public readonly ?string $specialRegime,
        public readonly ?string $immunityType,
        public readonly ?string $suspension,
        public readonly ?string $suspensionProcess,
        public readonly ?string $municipalBenefit,
        public readonly ?float $serviceValue,
        public readonly ?float $unconditionalDiscount,
        public readonly ?float $conditionalDiscount,
        public readonly ?float $deductions,
        public readonly ?float $benefitCalculation,
        public readonly ?float $issqnBase,
        public readonly ?float $issqnRate,
        public readonly ?string $issqnWithholding,
        public readonly bool $issqnWithheld,
        public readonly ?float $issqnValue,

        // Tributação federal
        public readonly ?float $irrf,
        public readonly ?float $cp,
        public readonly ?float $csll,
        public readonly ?float $pis,
        public readonly ?float $cofins,
        public readonly ?string $pisCofinsWithholding,
        public readonly ?float $totalFederalWithheld,

        // Totais
        public readonly ?float $netValue,
        public readonly ?string $approxFederalTaxes,
        public readonly ?string $approxStateTaxes,
        public readonly ?string $approxMunicipalTaxes,
        public readonly ?string $approxSimplesNacional,

        public readonly ?string $additionalInfo,

        // QR Code (PNG base64) e o conteúdo dele
        public readonly ?string $qrCodeUrl,
        public readonly ?string $qrCodeBase64,
    ) {
    }

    public function getFormattedAccessKey(): string
    {
        return trim(chunk_split($this->accessKey, 4, ' '));
    }
}

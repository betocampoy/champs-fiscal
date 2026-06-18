<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Transmission;

final class NfseTransmissionConfig
{
    // Emissão da DPS
    private const SEFIN_PROD    = 'https://sefin.nfse.gov.br/SefinNacional';
    private const SEFIN_HOMOLOG = 'https://sefin.producaorestrita.nfse.gov.br/SefinNacional';

    // Consulta e download (ADN Nacional)
    private const ADN_PROD      = 'https://adn.nfse.gov.br';
    private const ADN_HOMOLOG   = 'https://adn.producaorestrita.nfse.gov.br';

    public function __construct(
        private readonly string $environment = 'homolog',
    ) {
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isProduction(): bool
    {
        return $this->environment === 'prod' || $this->environment === '1';
    }

    private function sefin(): string
    {
        return $this->isProduction() ? self::SEFIN_PROD : self::SEFIN_HOMOLOG;
    }

    private function adn(): string
    {
        return $this->isProduction() ? self::ADN_PROD : self::ADN_HOMOLOG;
    }

    public function getEmitUrl(): string
    {
        return $this->sefin() . '/nfse';
    }

    public function getQueryUrl(string $accessKey): string
    {
        return $this->sefin() . '/nfse/' . $accessKey;
    }

    public function getEventsUrl(string $accessKey): string
    {
        return $this->sefin() . '/nfse/' . $accessKey . '/eventos';
    }

    public function getDanfseUrl(string $accessKey): string
    {
        return $this->adn() . '/danfse/' . $accessKey;
    }
}

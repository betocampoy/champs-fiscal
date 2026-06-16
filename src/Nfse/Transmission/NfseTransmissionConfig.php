<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Transmission;

final class NfseTransmissionConfig
{
    private const BASE_PROD    = 'https://adn.nfse.gov.br';
    private const BASE_HOMOLOG = 'https://adn.producaorestrita.nfse.gov.br';

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

    private function base(): string
    {
        return $this->isProduction() ? self::BASE_PROD : self::BASE_HOMOLOG;
    }

    public function getEmitUrl(): string
    {
        return $this->base() . '/nfse';
    }

    public function getQueryUrl(string $accessKey): string
    {
        return $this->base() . '/nfse/' . $accessKey;
    }

    public function getEventsUrl(string $accessKey): string
    {
        return $this->base() . '/nfse/' . $accessKey . '/eventos';
    }

    public function getDanfseUrl(string $accessKey): string
    {
        return $this->base() . '/danfse/' . $accessKey;
    }
}

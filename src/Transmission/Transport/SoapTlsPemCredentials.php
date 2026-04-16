<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

final class SoapTlsPemCredentials implements SoapTlsCredentialsInterface
{
    public function __construct(
        private readonly string $certificatePem,
        private readonly string $privateKeyPem,
        private readonly ?string $passphrase = null,
        private readonly ?string $caFile = null,
    ) {}

    public function getCertificatePem(): string
    {
        return $this->certificatePem;
    }

    public function getPrivateKeyPem(): string
    {
        return $this->privateKeyPem;
    }

    public function getPassphrase(): ?string
    {
        return $this->passphrase;
    }

    public function getCaFile(): ?string
    {
        return $this->caFile;
    }
}

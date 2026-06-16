<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

final class HttpTlsPemCredentials implements HttpTlsCredentialsInterface
{
    public function __construct(
        private readonly string $certificatePem,
        private readonly string $privateKeyPem,
        private readonly ?string $passphrase = null,
    ) {
    }

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
}

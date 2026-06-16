<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

interface HttpTlsCredentialsInterface
{
    public function getCertificatePem(): string;

    public function getPrivateKeyPem(): string;

    public function getPassphrase(): ?string;
}

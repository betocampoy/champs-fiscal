<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

interface SoapTlsCredentialsInterface
{
    public function getCertificatePem(): string;

    public function getPrivateKeyPem(): string;

    public function getPassphrase(): ?string;

    public function getCaFile(): ?string;
}

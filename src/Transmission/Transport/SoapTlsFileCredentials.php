<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

final class SoapTlsFileCredentials implements SoapTlsCredentialsInterface
{
    public function __construct(
        private readonly string $certificatePath,
        private readonly string $privateKeyPath,
        private readonly ?string $passphrase = null,
        private readonly ?string $caFile = null,
    ) {}

    public function getCertificatePem(): string
    {
        $content = @file_get_contents($this->certificatePath);

        if ($content === false) {
            throw new \RuntimeException(sprintf(
                'Não foi possível ler o certificado PEM em "%s".',
                $this->certificatePath
            ));
        }

        return $content;
    }

    public function getPrivateKeyPem(): string
    {
        $content = @file_get_contents($this->privateKeyPath);

        if ($content === false) {
            throw new \RuntimeException(sprintf(
                'Não foi possível ler a chave privada PEM em "%s".',
                $this->privateKeyPath
            ));
        }

        return $content;
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

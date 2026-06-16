<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

use RuntimeException;

final class HttpTlsTempFiles
{
    private ?string $certificatePath = null;
    private ?string $privateKeyPath = null;

    public function __construct(
        private readonly HttpTlsCredentialsInterface $credentials,
    ) {
    }

    public function create(): void
    {
        $this->certificatePath = tempnam(sys_get_temp_dir(), 'nfse_cert_');
        $this->privateKeyPath = tempnam(sys_get_temp_dir(), 'nfse_key_');

        if ($this->certificatePath === false || $this->privateKeyPath === false) {
            throw new RuntimeException('Falha ao criar arquivos temporários para certificado TLS.');
        }

        file_put_contents($this->certificatePath, $this->credentials->getCertificatePem());
        file_put_contents($this->privateKeyPath, $this->credentials->getPrivateKeyPem());
    }

    public function getCertificatePath(): string
    {
        return $this->certificatePath ?? throw new RuntimeException('Arquivos temporários não criados.');
    }

    public function getPrivateKeyPath(): string
    {
        return $this->privateKeyPath ?? throw new RuntimeException('Arquivos temporários não criados.');
    }

    public function cleanup(): void
    {
        if ($this->certificatePath !== null && file_exists($this->certificatePath)) {
            unlink($this->certificatePath);
        }

        if ($this->privateKeyPath !== null && file_exists($this->privateKeyPath)) {
            unlink($this->privateKeyPath);
        }
    }
}

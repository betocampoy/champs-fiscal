<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

final class SoapTlsTempFiles
{
    private ?string $certificatePath = null;
    private ?string $privateKeyPath = null;

    public function __construct(
        private readonly SoapTlsCredentialsInterface $credentials,
    ) {}

    public function create(): void
    {
        if ($this->certificatePath !== null || $this->privateKeyPath !== null) {
            return;
        }

        $certificatePath = tempnam(sys_get_temp_dir(), 'soap_cert_');
        $privateKeyPath = tempnam(sys_get_temp_dir(), 'soap_key_');

        if ($certificatePath === false || $privateKeyPath === false) {
            throw new \RuntimeException('Não foi possível criar arquivos temporários TLS.');
        }

        if (file_put_contents($certificatePath, $this->credentials->getCertificatePem()) === false) {
            @unlink($certificatePath);
            @unlink($privateKeyPath);

            throw new \RuntimeException('Não foi possível gravar o certificado PEM temporário.');
        }

        if (file_put_contents($privateKeyPath, $this->credentials->getPrivateKeyPem()) === false) {
            @unlink($certificatePath);
            @unlink($privateKeyPath);

            throw new \RuntimeException('Não foi possível gravar a chave privada PEM temporária.');
        }

        $this->restrictPermissions($certificatePath);
        $this->restrictPermissions($privateKeyPath);

        $this->certificatePath = $certificatePath;
        $this->privateKeyPath = $privateKeyPath;
    }

    public function getCertificatePath(): string
    {
        if ($this->certificatePath === null) {
            throw new \LogicException('O arquivo temporário do certificado ainda não foi criado.');
        }

        return $this->certificatePath;
    }

    public function getPrivateKeyPath(): string
    {
        if ($this->privateKeyPath === null) {
            throw new \LogicException('O arquivo temporário da chave privada ainda não foi criado.');
        }

        return $this->privateKeyPath;
    }

    public function cleanup(): void
    {
        if ($this->certificatePath !== null && is_file($this->certificatePath)) {
            @unlink($this->certificatePath);
        }

        if ($this->privateKeyPath !== null && is_file($this->privateKeyPath)) {
            @unlink($this->privateKeyPath);
        }

        $this->certificatePath = null;
        $this->privateKeyPath = null;
    }

    private function restrictPermissions(string $path): void
    {
        if (DIRECTORY_SEPARATOR === '/') {
            @chmod($path, 0600);
        }
    }
}

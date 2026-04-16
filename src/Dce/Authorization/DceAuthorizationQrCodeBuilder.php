<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Authorization;

use InvalidArgumentException;

final class DceAuthorizationQrCodeBuilder
{
    public function buildNormal(string $baseUrl, string $accessKey, int $tpAmb): string
    {
        $this->assertAccessKey($accessKey);
        $this->assertTpAmb($tpAmb);

        return $this->normalizeBaseUrl($baseUrl) . http_build_query([
                'chDCe' => $accessKey,
                'tpAmb' => $tpAmb,
            ], '', '&', PHP_QUERY_RFC3986);
    }

    public function buildOffline(
        string $baseUrl,
        string $accessKey,
        int $tpAmb,
        string $documentField,
        string $documentValue,
        string $sign,
    ): string {
        $this->assertAccessKey($accessKey);
        $this->assertTpAmb($tpAmb);
        $this->assertDocumentField($documentField);

        $documentValue = trim($documentValue);
        $sign = trim($sign);

        if ($documentValue === '') {
            throw new InvalidArgumentException('Valor do documento no QR Code offline é obrigatório.');
        }

        if ($sign === '') {
            throw new InvalidArgumentException('Assinatura do QR Code offline é obrigatória.');
        }

        return $this->normalizeBaseUrl($baseUrl) . http_build_query([
                'chDCe' => $accessKey,
                'tpAmb' => $tpAmb,
                $documentField => $documentValue,
                'sign' => $sign,
            ], '', '&', PHP_QUERY_RFC3986);
    }

    private function normalizeBaseUrl(string $baseUrl): string
    {
        $baseUrl = trim($baseUrl);

        if ($baseUrl === '') {
            throw new InvalidArgumentException('URL base do QR Code é obrigatória.');
        }

        return rtrim($baseUrl, '?&') . '?';
    }

    private function assertTpAmb(int $tpAmb): void
    {
        if (!in_array($tpAmb, [1, 2], true)) {
            throw new InvalidArgumentException('tpAmb inválido para QR Code.');
        }
    }

    private function assertAccessKey(string $accessKey): void
    {
        if (!preg_match('/^\d{44}$/', $accessKey)) {
            throw new InvalidArgumentException('Chave de acesso inválida para QR Code.');
        }
    }

    private function assertDocumentField(string $documentField): void
    {
        if (!in_array($documentField, ['CNPJ', 'CPF', 'idOutros'], true)) {
            throw new InvalidArgumentException('Campo de documento inválido para QR Code offline.');
        }
    }
}

<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Service;

use BetoCampoy\Champs\Fiscal\Dce\Dace\Contract\DaceQrCodeUrlBuilderInterface;
use InvalidArgumentException;

final class DaceQrCodeUrlBuilder implements DaceQrCodeUrlBuilderInterface
{
    public function buildNormal(
        string $baseUrl,
        string $accessKey,
        int $environment
    ): string {
        $accessKey = $this->sanitizeAccessKey($accessKey);
        $environment = $this->normalizeEnvironment($environment);

        return sprintf(
            '%s?chDCe=%s&tpAmb=%d',
            rtrim($baseUrl, '?'),
            $accessKey,
            $environment
        );
    }

    public function buildOfflineContingency(
        string $baseUrl,
        string $accessKey,
        int $environment,
        ?string $issuerDocument,
        string $signature
    ): string {
        $accessKey = $this->sanitizeAccessKey($accessKey);
        $environment = $this->normalizeEnvironment($environment);
        $signature = trim($signature);

        if ($signature === '') {
            throw new InvalidArgumentException('A assinatura do QR-Code em contingência é obrigatória.');
        }

        $documentParam = $this->buildIssuerDocumentParam($issuerDocument);

        return sprintf(
            '%s?chDCe=%s&tpAmb=%d%s&sign=%s',
            rtrim($baseUrl, '?'),
            $accessKey,
            $environment,
            $documentParam,
            rawurlencode($signature)
        );
    }

    private function sanitizeAccessKey(string $accessKey): string
    {
        $numbersOnly = preg_replace('/\D+/', '', $accessKey) ?? '';

        if (strlen($numbersOnly) !== 44) {
            throw new InvalidArgumentException('A chave de acesso deve conter 44 dígitos.');
        }

        return $numbersOnly;
    }

    private function normalizeEnvironment(int $environment): int
    {
        if (!in_array($environment, [1, 2], true)) {
            throw new InvalidArgumentException('Ambiente inválido para o QR-Code da DACE.');
        }

        return $environment;
    }

    private function buildIssuerDocumentParam(?string $issuerDocument): string
    {
        $numbersOnly = preg_replace('/\D+/', '', (string) $issuerDocument) ?? '';

        if (strlen($numbersOnly) === 14) {
            return '&CNPJ=' . $numbersOnly;
        }

        if (strlen($numbersOnly) === 11) {
            return '&CPF=' . $numbersOnly;
        }

        throw new InvalidArgumentException(
            'Documento do emitente inválido para QR-Code em contingência. Esperado CPF ou CNPJ.'
        );
    }
}

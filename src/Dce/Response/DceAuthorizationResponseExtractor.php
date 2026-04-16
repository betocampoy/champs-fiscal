<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Response;

use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;

final class DceAuthorizationResponseExtractor
{
    public function __construct(
        private readonly DocumentResponse $response,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        $payload = $this->response->getParsedValue('payload', []);

        return is_array($payload) ? $payload : [];
    }

    public function getResponseType(): ?string
    {
        $value = $this->response->getParsedValue('response_type');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function getPayloadRoot(): ?string
    {
        $value = $this->response->getParsedValue('payload_root');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function getStatusCode(): ?string
    {
        $value = $this->getPayload()['cStat'] ?? null;

        return $this->normalizeString($value);
    }

    public function getStatusMessage(): ?string
    {
        $value = $this->getPayload()['xMotivo'] ?? null;

        return $this->normalizeString($value);
    }

    public function getApplicationVersion(): ?string
    {
        $value = $this->getPayload()['verAplic'] ?? null;

        return $this->normalizeString($value);
    }

    public function getEnvironment(): ?string
    {
        $payloadValue = $this->getPayload()['tpAmb'] ?? null;
        $payloadEnvironment = $this->normalizeString($payloadValue);

        if ($payloadEnvironment !== null) {
            return $payloadEnvironment;
        }

        $parsedEnvironment = $this->response->getParsedValue('environment');

        return $this->normalizeString($parsedEnvironment);
    }

    public function getReceivedAt(): ?string
    {
        $value = $this->getPayload()['dhRecbto'] ?? null;

        return $this->normalizeString($value);
    }

    public function getAccessKey(): ?string
    {
        $infProt = $this->getInfProt();
        $value = $infProt['chDCe'] ?? null;

        if ($value !== null) {
            return $this->normalizeString($value);
        }

        return $this->normalizeString($this->response->getParsedValue('access_key'));
    }

    public function getProtocolNumber(): ?string
    {
        $value = $this->getInfProt()['nProt'] ?? null;

        return $this->normalizeString($value);
    }

    public function getDigestValue(): ?string
    {
        $value = $this->getInfProt()['digVal'] ?? null;

        return $this->normalizeString($value);
    }

    public function getRawXml(): ?string
    {
        return $this->normalizeString($this->response->getParsedValue('raw_xml'));
    }

    public function getRequestXml(): ?string
    {
        return $this->normalizeString($this->response->getParsedValue('request_xml'));
    }

    public function getResponseXml(): ?string
    {
        return $this->normalizeString($this->response->getParsedValue('response_xml'));
    }

    public function getRequestHeaders(): ?string
    {
        return $this->normalizeString($this->response->getParsedValue('request_headers'));
    }

    public function getResponseHeaders(): ?string
    {
        return $this->normalizeString($this->response->getParsedValue('response_headers'));
    }

    public function getUnsignedXml(): ?string
    {
        return $this->normalizeString($this->response->getParsedValue('xml'));
    }

    public function getSignedXml(): ?string
    {
        return $this->normalizeString($this->response->getParsedValue('signed_xml'));
    }

    public function isAuthorized(): bool
    {
        return $this->response->isSuccess() && $this->getStatusCode() === '100';
    }

    public function isRejected(): bool
    {
        return $this->response->isSuccess() && $this->getStatusCode() !== null && $this->getStatusCode() !== '100';
    }

    /**
     * @return array<string, mixed>
     */
    private function getInfProt(): array
    {
        $payload = $this->getPayload();
        $protDce = $payload['protDCe'] ?? null;

        if (!is_array($protDce)) {
            return [];
        }

        $infProt = $protDce['infProt'] ?? null;

        return is_array($infProt) ? $infProt : [];
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        if (is_scalar($value)) {
            $value = trim((string) $value);

            return $value === '' ? null : $value;
        }

        return null;
    }
}

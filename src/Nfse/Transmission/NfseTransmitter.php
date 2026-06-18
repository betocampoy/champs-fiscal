<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Transmission;

use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use BetoCampoy\Champs\Fiscal\Transmission\Transport\HttpTransport;
use BetoCampoy\Champs\Fiscal\Transmission\Transport\HttpTlsCredentialsInterface;
use Throwable;

final class NfseTransmitter
{
    public function __construct(
        private readonly HttpTransport $transport,
        private readonly NfseTransmissionConfig $config,
        private readonly NfseResponseParser $parser,
    ) {
    }

    public static function createForEnvironment(string $environment = 'homolog'): self
    {
        return new self(
            transport: new HttpTransport(),
            config: new NfseTransmissionConfig($environment),
            parser: new NfseResponseParser(),
        );
    }

    public function getEnvironment(): string
    {
        return $this->config->getEnvironment();
    }

    public function emit(
        string $dpsXmlGZipB64,
        HttpTlsCredentialsInterface $tlsCredentials,
    ): DocumentResponse {
        try {
            $response = $this->transport->post(
                url: $this->config->getEmitUrl(),
                body: ['dpsXmlGZipB64' => $dpsXmlGZipB64],
                tlsCredentials: $tlsCredentials,
            );

            $parsed = $this->parser->parse($response);

            return new DocumentResponse(
                success: $response->isSuccess(),
                rawResponse: $response->getBody(),
                parsed: array_merge($parsed, ['environment' => $this->config->getEnvironment()]),
                error: $response->isSuccess() ? null : ($parsed['status_msg'] ?? 'Erro na emissão da NFS-e.'),
            );
        } catch (Throwable $e) {
            return new DocumentResponse(
                success: false,
                rawResponse: '',
                parsed: ['environment' => $this->config->getEnvironment()],
                error: $e->getMessage(),
            );
        }
    }

    public function query(
        string $accessKey,
        HttpTlsCredentialsInterface $tlsCredentials,
    ): DocumentResponse {
        try {
            $response = $this->transport->get(
                url: $this->config->getQueryUrl($accessKey),
                tlsCredentials: $tlsCredentials,
            );

            $parsed = $this->parser->parse($response);

            return new DocumentResponse(
                success: $response->isSuccess(),
                rawResponse: $response->getBody(),
                parsed: array_merge($parsed, ['environment' => $this->config->getEnvironment()]),
                error: $response->isSuccess() ? null : ($parsed['status_msg'] ?? 'Erro na consulta da NFS-e.'),
            );
        } catch (Throwable $e) {
            return new DocumentResponse(
                success: false,
                rawResponse: '',
                parsed: ['environment' => $this->config->getEnvironment()],
                error: $e->getMessage(),
            );
        }
    }

    public function danfse(
        string $accessKey,
        HttpTlsCredentialsInterface $tlsCredentials,
    ): DocumentResponse {
        try {
            $response = $this->transport->get(
                url: $this->config->getDanfseUrl($accessKey),
                tlsCredentials: $tlsCredentials,
                headers: ['Accept' => 'text/html,application/xhtml+xml,application/pdf,*/*'],
            );

            $headers     = $response->getHeaders();
            $contentType = $headers['content-type'][0] ?? $headers['Content-Type'][0] ?? 'text/html; charset=utf-8';

            return new DocumentResponse(
                success: $response->isSuccess(),
                rawResponse: $response->getBody(),
                parsed: [
                    'content_type' => $contentType,
                    'http_status'  => $response->getStatusCode(),
                    'environment'  => $this->config->getEnvironment(),
                ],
                error: $response->isSuccess() ? null : "DANFSE indisponível (HTTP {$response->getStatusCode()}).",
            );
        } catch (Throwable $e) {
            return new DocumentResponse(
                success: false,
                rawResponse: '',
                parsed: ['environment' => $this->config->getEnvironment()],
                error: $e->getMessage(),
            );
        }
    }
}

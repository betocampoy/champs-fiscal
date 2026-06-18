<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpTransport implements HttpTransportInterface
{
    public function __construct(
        private readonly bool $verifyPeer = false,
        private readonly ?HttpClientInterface $client = null,
    ) {
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    public function post(
        string $url,
        array $body,
        HttpTlsCredentialsInterface $tlsCredentials,
        array $headers = [],
    ): HttpTransportResponse {
        $tempFiles = new HttpTlsTempFiles($tlsCredentials);
        $tempFiles->create();

        try {
            $client = $this->buildClient($tempFiles, $tlsCredentials);

            $response = $client->request('POST', $url, [
                'headers' => array_merge([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ], $headers),
                'json' => $body,
            ]);

            return new HttpTransportResponse(
                statusCode: $response->getStatusCode(),
                body: $response->getContent(false),
                headers: $response->getHeaders(false),
            );
        } catch (\Throwable $e) {
            throw new RuntimeException('Erro na transmissão HTTP: ' . $e->getMessage(), 0, $e);
        } finally {
            $tempFiles->cleanup();
        }
    }

    public function get(
        string $url,
        HttpTlsCredentialsInterface $tlsCredentials,
        array $headers = [],
    ): HttpTransportResponse {
        $tempFiles = new HttpTlsTempFiles($tlsCredentials);
        $tempFiles->create();

        try {
            $client = $this->buildClient($tempFiles, $tlsCredentials);

            $response = $client->request('GET', $url, [
                'headers' => array_merge(['Accept' => 'application/json'], $headers),
            ]);

            return new HttpTransportResponse(
                statusCode: $response->getStatusCode(),
                body: $response->getContent(false),
                headers: $response->getHeaders(false),
            );
        } catch (\Throwable $e) {
            throw new RuntimeException('Erro na consulta HTTP: ' . $e->getMessage(), 0, $e);
        } finally {
            $tempFiles->cleanup();
        }
    }

    private function buildClient(
        HttpTlsTempFiles $tempFiles,
        HttpTlsCredentialsInterface $credentials,
    ): HttpClientInterface {
        if ($this->client !== null) {
            return $this->client;
        }

        $options = [
            'verify_peer' => $this->verifyPeer,
            'verify_host' => $this->verifyPeer,
            'local_cert' => $tempFiles->getCertificatePath(),
            'local_pk' => $tempFiles->getPrivateKeyPath(),
        ];

        if ($credentials->getPassphrase() !== null) {
            $options['passphrase'] = $credentials->getPassphrase();
        }

        return HttpClient::create($options);
    }
}

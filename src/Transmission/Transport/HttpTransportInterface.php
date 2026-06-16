<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

interface HttpTransportInterface
{
    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    public function post(
        string $url,
        array $body,
        HttpTlsCredentialsInterface $tlsCredentials,
        array $headers = [],
    ): HttpTransportResponse;

    public function get(
        string $url,
        HttpTlsCredentialsInterface $tlsCredentials,
    ): HttpTransportResponse;
}

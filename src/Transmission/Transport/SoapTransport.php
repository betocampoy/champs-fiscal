<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

final class SoapTransport
{
    public function __construct(
        private readonly bool $verifyPeer = false,
    )
    {
    }

    /**
     * @param array<int, mixed> $arguments
     * @param array<string, mixed> $soapOptions
     */
    public function call(
        string                      $wsdl,
        string                      $operation,
        array                       $arguments,
        SoapTlsCredentialsInterface $tlsCredentials,
        array                       $soapOptions = [],
    ): SoapTransportResponse
    {
        $tempFiles = new SoapTlsTempFiles($tlsCredentials);
        $tempFiles->create();

        try {
            $sslOptions = [
                'local_cert' => $tempFiles->getCertificatePath(),
                'local_pk' => $tempFiles->getPrivateKeyPath(),
                'verify_peer' => $this->verifyPeer,
                'verify_peer_name' => $this->verifyPeer,
                'allow_self_signed' => false,
                'SNI_enabled' => true,
                'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
            ];

            if ($tlsCredentials->getPassphrase() !== null) {
                $sslOptions['passphrase'] = $tlsCredentials->getPassphrase();
            }

            if ($tlsCredentials->getCaFile() !== null) {
                $sslOptions['cafile'] = $tlsCredentials->getCaFile();
            }

            $streamContext = stream_context_create([
                'ssl' => $sslOptions,
            ]);

            $client = new \SoapClient($wsdl, array_replace([
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => $streamContext,
                'soap_version' => SOAP_1_2,
            ], $soapOptions));

            try {
                $result = $client->__soapCall($operation, $arguments);
            } catch (\Throwable $e) {
                dump($e->getMessage());
                dump($client->__getLastRequestHeaders());
                dump($client->__getLastRequest());
                dump($client->__getLastResponseHeaders());
                dump($client->__getLastResponse());

                throw $e;
            }

            return new SoapTransportResponse(
                result: $result,
                requestXml: $client->__getLastRequest() ?: null,
                responseXml: $client->__getLastResponse() ?: null,
                requestHeaders: $client->__getLastRequestHeaders() ?: null,
                responseHeaders: $client->__getLastResponseHeaders() ?: null,
            );
        } finally {
            $tempFiles->cleanup();
        }
    }
}

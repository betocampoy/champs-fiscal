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

            // PHP 8.x desabilita carregamento de entidades externas via libxml por padrão,
            // impedindo SoapClient de buscar o WSDL via URL ("failed to load external entity").
            // Quando o WSDL é um arquivo local, passa diretamente. Quando é URL, pré-busca
            // com file_get_contents + stream_context e usa arquivo temporário local.
            $wsdlIsLocal = file_exists($wsdl);

            if (!$wsdlIsLocal) {
                $wsdlContent = @file_get_contents($wsdl, false, $streamContext);
                if ($wsdlContent === false || trim($wsdlContent) === '') {
                    throw new \RuntimeException("Falha ao carregar WSDL de '{$wsdl}'.");
                }
                $wsdlTempFile = tempnam(sys_get_temp_dir(), 'champs_wsdl_');
                file_put_contents($wsdlTempFile, $wsdlContent);
                $wsdlPath = $wsdlTempFile;
            } else {
                $wsdlTempFile = null;
                $wsdlPath = $wsdl;
            }

            try {
                $client = new \SoapClient($wsdlPath, array_replace([
                    'trace' => true,
                    'exceptions' => true,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'stream_context' => $streamContext,
                    'soap_version' => SOAP_1_2,
                ], $soapOptions));
            } finally {
                if ($wsdlTempFile !== null) {
                    unlink($wsdlTempFile);
                }
            }

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

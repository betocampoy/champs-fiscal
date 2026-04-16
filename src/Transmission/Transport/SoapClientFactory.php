<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

final class SoapClientFactory
{
    /**
     * @param array<string, mixed> $options
     */
    public function createSession(
        string $wsdl,
        SoapTlsPemCredentials $credentials,
        array $options = [],
        bool $verifyPeer = false,
    ): SoapClientSession {
        $tempFiles = new SoapTlsTempFiles($credentials);
        $tempFiles->create();

        try {
            $sslOptions = [
                'local_cert' => $tempFiles->getCertificatePath(),
                'local_pk' => $tempFiles->getPrivateKeyPath(),
                'verify_peer' => $verifyPeer,
                'verify_peer_name' => $verifyPeer,
                'allow_self_signed' => false,
                'SNI_enabled' => true,
                'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
            ];

            if ($credentials->getPassphrase() !== null) {
                $sslOptions['passphrase'] = $credentials->getPassphrase();
            }

            if ($credentials->getCaFile() !== null) {
                $sslOptions['cafile'] = $credentials->getCaFile();
            }

            $streamContext = stream_context_create([
                'ssl' => $sslOptions,
            ]);

            $soapOptions = array_replace([
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => $streamContext,
            ], $options);

            $client = new \SoapClient($wsdl, $soapOptions);

            return new SoapClientSession($client, $tempFiles);
        } catch (\Throwable $e) {
            $tempFiles->cleanup();
            throw $e;
        }
    }
}

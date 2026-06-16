<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Query;

use BetoCampoy\Champs\Fiscal\Nfse\Transmission\NfseTransmitter;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use BetoCampoy\Champs\Fiscal\Transmission\Transport\HttpTlsPemCredentials;
use BetoCampoy\Champs\Certificate\ValueObject\OpenedCertificateData;
use Throwable;

final class NfseQueryService
{
    private NfseTransmitter $transmitter;

    public function __construct(
        string $environment = 'homolog',
        ?NfseTransmitter $transmitter = null,
    ) {
        $this->transmitter = $transmitter ?? NfseTransmitter::createForEnvironment($environment);
    }

    public function query(
        string $accessKey,
        OpenedCertificateData $certificate,
    ): DocumentResponse {
        try {
            $tlsCredentials = new HttpTlsPemCredentials(
                certificatePem: $certificate->getCertificate(),
                privateKeyPem: $certificate->getPrivateKey(),
            );

            return $this->transmitter->query($accessKey, $tlsCredentials);

        } catch (Throwable $e) {
            return new DocumentResponse(
                success: false,
                rawResponse: '',
                parsed: null,
                error: $e->getMessage(),
            );
        }
    }
}

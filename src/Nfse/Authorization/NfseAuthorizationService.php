<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Authorization;

use BetoCampoy\Champs\Certificate\ValueObject\OpenedCertificateData;
use BetoCampoy\Champs\Fiscal\Nfse\Request\Builder\NfseAuthorizationPayload;
use BetoCampoy\Champs\Fiscal\Nfse\Signer\NfseSignatureConfigFactory;
use BetoCampoy\Champs\Fiscal\Nfse\Transmission\NfseTransmitter;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use BetoCampoy\Champs\Fiscal\Transmission\Transport\HttpTlsPemCredentials;
use BetoCampoy\Champs\Fiscal\Xml\XmlSigner;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class NfseAuthorizationService
{
    private readonly int $environmentCode;
    private NfseAuthorizationDataFactory $dataFactory;
    private NfseAuthorizationXmlBuilder $xmlBuilder;
    private XmlSigner $signer;
    private NfseTransmitter $transmitter;

    public function __construct(
        string $environment = 'homolog',
        ?NfseAuthorizationDataFactory $dataFactory = null,
        ?NfseAuthorizationXmlBuilder $xmlBuilder = null,
        ?XmlSigner $signer = null,
        ?NfseTransmitter $transmitter = null,
    ) {
        $this->environmentCode = ($environment === 'prod' || $environment === '1') ? 1 : 2;
        $this->dataFactory     = $dataFactory ?? new NfseAuthorizationDataFactory();
        $this->xmlBuilder      = $xmlBuilder  ?? new NfseAuthorizationXmlBuilder();
        $this->signer          = $signer      ?? new XmlSigner();
        $this->transmitter     = $transmitter ?? NfseTransmitter::createForEnvironment($environment);
    }

    public function getEnvironmentCode(): int
    {
        return $this->environmentCode;
    }

    public function emit(
        NfseAuthorizationPayload $payload,
        OpenedCertificateData $certificate,
    ): DocumentResponse {
        try {
            $this->assertCertificate($certificate);

            $data = $this->dataFactory->create($payload);

            $xml = $this->xmlBuilder->build($data);
            
            $signedXml = $this->signer->sign(
                xml: $xml,
                referenceId: $data->getDpsId(),
                privateKeyPem: $certificate->getPrivateKey(),
                certificatePem: $certificate->getCertificate(),
                config: NfseSignatureConfigFactory::makeForDps(),
            );

            $signedXml = $this->forceXmlUtf8Declaration($signedXml);

            $compressed = gzencode($signedXml);
            if ($compressed === false) {
                throw new RuntimeException('Falha ao comprimir o XML da DPS (gzencode).');
            }

            $dpsXmlGZipB64 = base64_encode($compressed);

            $tlsCredentials = new HttpTlsPemCredentials(
                certificatePem: $certificate->getCertificate(),
                privateKeyPem: $certificate->getPrivateKey(),
            );

            return $this->transmitter->emit($dpsXmlGZipB64, $tlsCredentials);
        } catch (Throwable $e) {
            return new DocumentResponse(
                success: false,
                rawResponse: '',
                parsed: null,
                error: $e->getMessage(),
            );
        }
    }

    private function assertCertificate(OpenedCertificateData $certificate): void
    {
        if (trim($certificate->getCertificate()) === '') {
            throw new InvalidArgumentException('Certificado digital não contém o certificado PEM.');
        }

        if (trim($certificate->getPrivateKey()) === '') {
            throw new InvalidArgumentException('Certificado digital não contém a chave privada PEM.');
        }
    }

    private function forceXmlUtf8Declaration(string $xml): string
    {
        $xml = ltrim($xml);

        $xml = preg_replace(
            '/^<\?xml\s+version="1\.0"\s*(?:encoding="[^"]*")?\s*\?>/i',
            '',
            $xml
        );

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . ltrim((string) $xml);
    }
}

<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Event\Cancel;

use BetoCampoy\Champs\Certificate\ValueObject\OpenedCertificateData;
use BetoCampoy\Champs\Fiscal\Dce\Event\DceEventXsdValidator;
use BetoCampoy\Champs\Fiscal\Dce\Request\Event\Builder\DceEventPayload;
use BetoCampoy\Champs\Fiscal\Dce\Schema\DceSchemaLocator;
use BetoCampoy\Champs\Fiscal\Dce\Signer\DceSignatureConfigFactory;
use BetoCampoy\Champs\Fiscal\Dce\Signer\DceSigner;
use BetoCampoy\Champs\Fiscal\Dce\Transmission\DceTransmitter;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentOperation;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentRequest;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use BetoCampoy\Champs\Fiscal\Transmission\Transport\SoapTlsPemCredentials;
use BetoCampoy\Champs\Fiscal\Xml\XmlSigner;
use InvalidArgumentException;
use Throwable;

final class DceCancelService
{
    private DceCancelDataFactory $dataFactory;
    private DceCancelXmlBuilder $xmlBuilder;
    private DceSigner $signer;
    private DceSchemaLocator $schemaLocator;
    private DceEventXsdValidator $xsdValidator;
    private DceTransmitter $transmitter;

    public function __construct(
        string $environment = 'homolog',
        ?DceCancelDataFactory $dataFactory = null,
        ?DceCancelXmlBuilder $xmlBuilder = null,
        ?DceSigner $signer = null,
        ?DceSchemaLocator $schemaLocator = null,
        ?DceEventXsdValidator $xsdValidator = null,
        ?DceTransmitter $transmitter = null,
    ) {
        $this->dataFactory = $dataFactory ?? new DceCancelDataFactory();

        $this->xmlBuilder = $xmlBuilder ?? new DceCancelXmlBuilder();
        $this->signer = $signer ?? new DceSigner(new XmlSigner());
        $this->schemaLocator = $schemaLocator ?? new DceSchemaLocator();
        $this->xsdValidator = $xsdValidator ?? new DceEventXsdValidator();
        $this->transmitter = $transmitter ?? DceTransmitter::createForEnvironment($environment);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function send(
        DceEventPayload $payload,
        OpenedCertificateData $certificate,
    ): DocumentResponse {
        try {
            $this->assertInput($payload);
            $this->assertCertificate($certificate);
            
            $data = $this->dataFactory->create($payload);
            
            $xml = $this->xmlBuilder->build($data);

            $privateKeyPem = $certificate->getPrivateKey();
            $leafCertPem   = $certificate->getCertificate();

            $signedXml = $this->signer->sign(
                xml: $xml,
                referenceId: $data->getSignatureReferenceId(),
                privateKeyPem: $privateKeyPem,
                certificatePem: $leafCertPem,
                config: DceSignatureConfigFactory::makeForCancelEvent(),
            );

            $xsdPath = $this->schemaLocator->getEventXsd($data->versao);
            $this->xsdValidator->validate($signedXml, $xsdPath);

            $request = new DocumentRequest(
                xml: $signedXml,
                operation: DocumentOperation::CANCEL,
            );

            $extraCerts   = $certificate->getExtraCertificates();
            $chainCertPem = $leafCertPem;
            if (!empty($extraCerts)) {
                $chainCertPem .= "\n" . implode("\n", $extraCerts);
            }

            $soapCredentials = new SoapTlsPemCredentials(
                certificatePem: $chainCertPem,
                privateKeyPem: $privateKeyPem,
            );

            $response = $this->transmitter->transmit($request, $soapCredentials);

            if (!$response->isSuccess()) {
                return $response;
            }

            return $response->withMergedParsed([
                'xml' => $xml,
                'signed_xml' => $signedXml,
                'access_key' => $data->getEventId(),
            ]);

        } catch (Throwable $e) {
            return new DocumentResponse(
                success: false,
                rawResponse: '',
                parsed: null,
                error: $e->getMessage(),
            );
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    private function assertInput(DceEventPayload $input): void
    {
        if ($input === []) {
            throw new InvalidArgumentException('Payload não informado para autorização da DC-e.');
        }
    }

    private function assertCertificate(OpenedCertificateData $certificate): void
    {
        $certificatePem = trim($certificate->getCertificate());
        $privateKeyPem = trim($certificate->getPrivateKey());

        if ($certificatePem === '') {
            throw new InvalidArgumentException('Certificado digital não contém o certificado PEM.');
        }

        if ($privateKeyPem === '') {
            throw new InvalidArgumentException('Certificado digital não contém a chave privada PEM.');
        }
    }
}

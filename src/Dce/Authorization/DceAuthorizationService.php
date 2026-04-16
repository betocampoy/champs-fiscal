<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Authorization;

use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Builder\DceAuthorizationPayload;
use BetoCampoy\Champs\Fiscal\Dce\Schema\DceSchemaLocator;
use BetoCampoy\Champs\Fiscal\Dce\Signer\DceSigner;
use BetoCampoy\Champs\Fiscal\Dce\Signer\DceSignatureConfigFactory;
use BetoCampoy\Champs\Fiscal\Dce\Transmission\DceTransmitter;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentOperation;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentRequest;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use BetoCampoy\Champs\Fiscal\Transmission\Transport\SoapTlsPemCredentials;
use BetoCampoy\Champs\Fiscal\Xml\XmlSigner;
use BetoCampoy\Champs\Certificate\ValueObject\OpenedCertificateData;
use InvalidArgumentException;
use Throwable;

final class DceAuthorizationService
{
    private DceAuthorizationDataFactory $dataFactory;
    private DceAuthorizationBusinessValidator $businessValidator;
    private DceAuthorizationXmlBuilder $xmlBuilder;
    private DceSigner $signer;
    private DceSchemaLocator $schemaLocator;
    private DceAuthorizationXsdValidator $xsdValidator;
    private DceTransmitter $transmitter;

    public function __construct(
        string                             $environment = 'homolog',
        ?DceAuthorizationDataFactory       $dataFactory = null,
        ?DceAuthorizationBusinessValidator $businessValidator = null,
        ?DceAuthorizationXmlBuilder        $xmlBuilder = null,
        ?DceSigner                         $signer = null,
        ?DceSchemaLocator                  $schemaLocator = null,
        ?DceAuthorizationXsdValidator      $xsdValidator = null,
        ?DceTransmitter                    $transmitter = null,
    )
    {
        $this->dataFactory = $dataFactory ?? new DceAuthorizationDataFactory(
            new DceAccessKeyGenerator(),
            new DceAuthorizationQrCodeBuilder(),
        );

        $this->businessValidator = $businessValidator ?? new DceAuthorizationBusinessValidator();
        $this->xmlBuilder = $xmlBuilder ?? new DceAuthorizationXmlBuilder();
        $this->signer = $signer ?? new DceSigner(new XmlSigner());
        $this->schemaLocator = $schemaLocator ?? new DceSchemaLocator();
        $this->xsdValidator = $xsdValidator ?? new DceAuthorizationXsdValidator();
        $this->transmitter = $transmitter ?? DceTransmitter::createForEnvironment($environment);
    }

    /**
     * @param DceAuthorizationPayload $payload
     */
    public function authorize(
        DceAuthorizationPayload $payload,
        OpenedCertificateData   $certificate,
    ): DocumentResponse
    {
        try {
            $this->assertPayload($payload);
            $this->assertCertificate($certificate);
            $data = $this->dataFactory->create($payload);

            $this->businessValidator->validate($data);

            $xml = $this->xmlBuilder->build($data);

            $privateKeyPem = $certificate->getPrivateKey();
            $certificatePem = $certificate->getCertificate();

            $signedXml = $this->signer->sign(
                xml: $xml,
                referenceId: $data->getSignatureReferenceId(),
                privateKeyPem: $privateKeyPem,
                certificatePem: $certificatePem,
                config: DceSignatureConfigFactory::makeForAuthorization(),
            );

            $xsdPath = $this->schemaLocator->getEmissionXsd($data->versao);
            $this->xsdValidator->validate($signedXml, $xsdPath);

            $request = new DocumentRequest(
                xml: $signedXml,
                operation: DocumentOperation::SEND,
            );

            $soapCredentials = new SoapTlsPemCredentials(
                certificatePem: $certificatePem,
                privateKeyPem: $privateKeyPem,
            );

            $response = $this->transmitter->transmit($request, $soapCredentials);

            if (!$response->isSuccess()) {
                return $response;
            }

            return $response->withMergedParsed([
                'xml' => $xml,
                'signed_xml' => $signedXml,
                'access_key' => $data->accessKey,
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

    private function assertPayload(
        DceAuthorizationPayload $payload
    ): void
    {
        if ($payload->all() === []) {
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

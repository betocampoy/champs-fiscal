<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Query;


use BetoCampoy\Champs\Certificate\ValueObject\OpenedCertificateData;
use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Builder\DceQueryPayload;
use BetoCampoy\Champs\Fiscal\Dce\Schema\DceSchemaLocator;
use BetoCampoy\Champs\Fiscal\Dce\Transmission\DceTransmitter;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentOperation;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentRequest;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use BetoCampoy\Champs\Fiscal\Transmission\Transport\SoapTlsPemCredentials;
use InvalidArgumentException;
use Throwable;

final class DceQueryService
{
    private DceQueryDataFactory $dataFactory;
    private DceQueryXmlBuilder $xmlBuilder;
    private DceSchemaLocator $schemaLocator;
    private DceQueryXsdValidator $xsdValidator;
    private DceTransmitter $transmitter;

    public function __construct(
        string $environment = 'homolog',
        ?DceQueryDataFactory $dataFactory = null,
        ?DceQueryXmlBuilder $xmlBuilder = null,
        ?DceSchemaLocator $schemaLocator = null,
        ?DceQueryXsdValidator $xsdValidator = null,
        ?DceTransmitter $transmitter = null,
    ) {
        $this->dataFactory = $dataFactory ?? new DceQueryDataFactory();

        $this->xmlBuilder = $xmlBuilder ?? new DceQueryXmlBuilder();
        $this->schemaLocator = $schemaLocator ?? new DceSchemaLocator();
        $this->xsdValidator = $xsdValidator ?? new DceQueryXsdValidator();
        $this->transmitter = $transmitter ?? DceTransmitter::createForEnvironment($environment);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function query(
        DceQueryPayload $payload,
        OpenedCertificateData $certificate,
    ): DocumentResponse {
        try {
            $this->assertInput($payload);
            $this->assertCertificate($certificate);

            $data = $this->dataFactory->create($payload);

            $xml = $this->xmlBuilder->build($data);

            $privateKeyPem = $certificate->getPrivateKey();
            $certificatePem = $certificate->getCertificate();

            $xsdPath = $this->schemaLocator->getQueryXsd($data->versao);
            $this->xsdValidator->validate($xml, $xsdPath);

            $request = new DocumentRequest(
                xml: $xml,
                operation: DocumentOperation::QUERY,
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
                'signed_xml' => $xml,
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
    private function assertInput(DceQueryPayload $input): void
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

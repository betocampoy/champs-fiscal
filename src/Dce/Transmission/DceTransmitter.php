<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Transmission;

use BetoCampoy\Champs\Fiscal\Dce\Response\XmlResponseFactory;
use BetoCampoy\Champs\Fiscal\Transmission\Contract\DocumentTransmitterInterface;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentOperation;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentRequest;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use BetoCampoy\Champs\Fiscal\Transmission\Transport\SoapTlsCredentialsInterface;
use BetoCampoy\Champs\Fiscal\Transmission\Transport\SoapTransport;
use RuntimeException;
use Throwable;

final class DceTransmitter implements DocumentTransmitterInterface
{
    public function __construct(
        private readonly SoapTransport $transport,
        private readonly DceTransmissionConfig $config,
        private readonly DceResponseParser $parser,
        private readonly DceAuthorizationPayloadBuilder $sendPayloadBuilder,
        private readonly DceQueryPayloadBuilder $queryPayloadBuilder,
        private readonly DceEventPayloadBuilder $eventPayloadBuilder,
        private readonly XmlResponseFactory $xmlResponseFactory,
    ) {
    }

    public static function createForEnvironment(string $environment = 'homolog'): self
    {
        return new self(
            transport: new SoapTransport(),
            config: new DceTransmissionConfig($environment),
            parser: new DceResponseParser(),
            sendPayloadBuilder: new DceAuthorizationPayloadBuilder(),
            queryPayloadBuilder: new DceQueryPayloadBuilder(),
            eventPayloadBuilder: new DceEventPayloadBuilder(),
            xmlResponseFactory: new XmlResponseFactory(),
        );
    }

    public function getEnvironment(): string
    {
        return $this->config->getEnvironment();
    }

    public function transmit(
        DocumentRequest $request,
        SoapTlsCredentialsInterface $tlsCredentials,
    ): DocumentResponse {
        try {
            $payload = $this->buildPayload($request);

            $transportResponse = $this->transport->call(
                wsdl: $this->resolveWsdl($request),
                operation: $this->resolveSoapOperation($request),
                arguments: [$payload],
                tlsCredentials: $tlsCredentials,
            );

            $rawResponse = $transportResponse->getResponseXml() ?? '';

            if (trim($rawResponse) === '') {
                throw new RuntimeException('A SEFAZ não retornou um XML de resposta.');
            }

            $parsed = array_merge(
                $this->parser->parse($rawResponse),
                [
                    'request_xml' => $transportResponse->getRequestXml(),
                    'response_xml' => $transportResponse->getResponseXml(),
                    'request_headers' => $transportResponse->getRequestHeaders(),
                    'response_headers' => $transportResponse->getResponseHeaders(),
                    'environment' => $this->config->getEnvironment(),
                    'operation' => $request->getOperation()->value,
                ]
            );

//            $xmlResponseData = $this->extractXmlResponseData($parsed);

            $payload = $parsed['payload'] ?? null;

            $xmlResponse = is_array($payload)
                ? $this->xmlResponseFactory->create($request->getOperation(), $payload)
                : null;

            return new DocumentResponse(
                success: true,
                rawResponse: $rawResponse,
                parsed: $parsed,
                error: null,
                xmlResponse: $xmlResponse,
            );
        } catch (Throwable $e) {
            return new DocumentResponse(
                success: false,
                rawResponse: '',
                parsed: [
                    'exception_class' => $e::class,
                    'environment' => $this->config->getEnvironment(),
                    'operation' => $request->getOperation()->value,
                ],
                error: $e->getMessage(),
                xmlResponse: null,
            );
        }
    }

    private function buildPayload(DocumentRequest $request): object
    {
        return match ($request->getOperation()) {
            DocumentOperation::SEND,
            DocumentOperation::RECEPCAO => $this->sendPayloadBuilder->build($request),

            DocumentOperation::QUERY => $this->queryPayloadBuilder->build($request),

            DocumentOperation::CANCEL => $this->eventPayloadBuilder->build($request),
        };
    }

    private function resolveWsdl(DocumentRequest $request): string
    {
        return match ($request->getOperation()) {
            DocumentOperation::SEND,
            DocumentOperation::RECEPCAO => $this->config->getAuthorizationWsdl(),

            DocumentOperation::QUERY => $this->config->getQueryWsdl(),

            DocumentOperation::CANCEL => $this->config->getEventWsdl(),
        };
    }

    private function resolveSoapOperation(DocumentRequest $request): string
    {
        return match ($request->getOperation()) {
            DocumentOperation::SEND,
            DocumentOperation::RECEPCAO => $this->config->getAuthorizationOperation(),

            DocumentOperation::QUERY => $this->config->getQueryOperation(),

            DocumentOperation::CANCEL => $this->config->getEventOperation(),
        };
    }

    /**
     * @param array<string, mixed> $parsed
     * @return array<string, mixed>|null
     */
    private function extractXmlResponseData(array $parsed): ?array
    {
        $xml = null;

        foreach (['response_xml', 'xmlResponse', 'raw_xml', 'any'] as $key) {
            if (isset($parsed[$key]) && is_string($parsed[$key]) && trim($parsed[$key]) !== '') {
                $xml = $parsed[$key];
                break;
            }
        }

        if ($xml === null) {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $root = simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NOCDATA);

            if ($root === false) {
                return null;
            }

            $body = $this->findSoapBody($root);

            if ($body === null) {
                return null;
            }

            $resultNode = $this->getFirstElementChild($body);

            if ($resultNode === null) {
                return null;
            }

            $payloadNode = $this->getFirstElementChild($resultNode);

            if ($payloadNode === null) {
                return null;
            }

            $json = json_encode($payloadNode);

            if ($json === false) {
                return null;
            }

            $decoded = json_decode($json, true);

            return is_array($decoded) ? $decoded : null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function findSoapBody(\SimpleXMLElement $root): ?\SimpleXMLElement
    {
        $namespaces = $root->getNamespaces(true);

        foreach (['env', 'soap'] as $prefix) {
            if (!isset($namespaces[$prefix])) {
                continue;
            }

            $children = $root->children($namespaces[$prefix]);

            if (isset($children->Body)) {
                return $children->Body;
            }
        }

        if (isset($root->Body)) {
            return $root->Body;
        }

        return null;
    }

    private function getFirstElementChild(\SimpleXMLElement $element): ?\SimpleXMLElement
    {
        foreach ($element->children() as $child) {
            return $child;
        }

        $namespaces = $element->getNamespaces(true);

        foreach ($namespaces as $namespace) {
            foreach ($element->children($namespace) as $child) {
                return $child;
            }
        }

        return null;
    }
}

<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Transmission;

use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use SoapClient;
use SoapVar;
use stdClass;
use Throwable;

final class DceQueryTransmitter
{
    public function __construct(
        private readonly SoapClient $soapClient,
    ) {
    }

    public function transmit(string $xml): DocumentResponse
    {
        try {
            $payload = new stdClass();
            $payload->dceDadosMsg = new SoapVar($xml, XSD_ANYXML);

            $response = $this->soapClient->__soapCall(
                'dceConsultaDCe',
                [$payload]
            );

            $rawResponse = $this->extractRawResponse($response);

            return new DocumentResponse(
                success: true,
                rawResponse: $rawResponse,
                parsed: null,
                error: null,
            );
        } catch (Throwable $e) {
            return new DocumentResponse(
                success: false,
                rawResponse: '',
                parsed: null,
                error: $e->getMessage(),
            );
        }
    }

    private function extractRawResponse(object|string|null $response): string
    {
        if (is_string($response)) {
            return $response;
        }

        if (!is_object($response)) {
            return '';
        }

        foreach ([
                     'dceConsultaDCeResult',
                     'retConsSitDCe',
                     'return',
                 ] as $property) {
            if (isset($response->{$property}) && is_string($response->{$property})) {
                return $response->{$property};
            }
        }

        return '';
    }
}

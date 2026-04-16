<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

final class SoapTransportResponse
{
    public function __construct(
        private readonly mixed $result,
        private readonly ?string $requestXml,
        private readonly ?string $responseXml,
        private readonly ?string $requestHeaders,
        private readonly ?string $responseHeaders,
    ) {}

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function getRequestXml(): ?string
    {
        return $this->requestXml;
    }

    public function getResponseXml(): ?string
    {
        return $this->responseXml;
    }

    public function getRequestHeaders(): ?string
    {
        return $this->requestHeaders;
    }

    public function getResponseHeaders(): ?string
    {
        return $this->responseHeaders;
    }
}

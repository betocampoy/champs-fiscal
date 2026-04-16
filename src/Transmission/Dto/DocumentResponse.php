<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Dto;

use BetoCampoy\Champs\Fiscal\Dce\Response\XmlResponse;

final class DocumentResponse
{
    public function __construct(
        private readonly bool $success,
        private readonly string $rawResponse,
        private readonly ?array $parsed = null,
        private readonly ?string $error = null,
        private readonly ?XmlResponse $xmlResponse = null
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getRawResponse(): string
    {
        return $this->rawResponse;
    }

    public function getParsed(): ?array
    {
        return $this->parsed;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getParsedValue(string $key, mixed $default = null): mixed
    {
        return $this->parsed[$key] ?? $default;
    }

    public function getXmlResponse(): ?XmlResponse
    {
        return $this->xmlResponse;
    }

    public function withParsed(?array $parsed): self
    {
        return new self(
            success: $this->success,
            rawResponse: $this->rawResponse,
            parsed: $parsed,
            error: $this->error,
            xmlResponse: $this->xmlResponse,
        );
    }

    public function withMergedParsed(array $extra): self
    {
        return new self(
            success: $this->success,
            rawResponse: $this->rawResponse,
            parsed: array_merge($this->parsed ?? [], $extra),
            error: $this->error,
            xmlResponse: $this->xmlResponse,
        );
    }

    public function withXmlResponse(?XmlResponse $xmlResponse): self
    {
        return new self(
            success: $this->success,
            rawResponse: $this->rawResponse,
            parsed: $this->parsed,
            error: $this->error,
            xmlResponse: $xmlResponse,
        );
    }
}

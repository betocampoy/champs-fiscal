<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Dto;

final class DocumentRequest
{
    public function __construct(
        private readonly string $xml,
        private readonly DocumentOperation $operation,
        private readonly array $metadata = []
    ) {
        $this->assertValid();
    }

    public function getXml(): string
    {
        return $this->xml;
    }

    public function getOperation(): DocumentOperation
    {
        return $this->operation;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    private function assertValid(): void
    {
        if (trim($this->xml) === '') {
            throw new \InvalidArgumentException('O XML do documento é obrigatório.');
        }
    }
}

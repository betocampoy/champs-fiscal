<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Response;

abstract class XmlResponse
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        protected readonly array $data
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function getValue(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }


    public function getStatusCode(): ?string
    {
        $value = $this->data['cStat'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getStatusMessage(): ?string
    {
        $value = $this->data['xMotivo'] ?? null;

        return $value === null ? null : (string) $value;
    }
}

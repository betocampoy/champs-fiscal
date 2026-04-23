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

    public function getEnvironment(): ?string
    {
        $value = $this->data['tpAmb'] ?? null;
        return $value === null ? null : (string) $value;
    }

    public function getApplicationVersion(): ?string
    {
        $value = $this->data['verAplic'] ?? null;
        return $value === null ? null : (string) $value;
    }

    public function getUfCode(): ?string
    {
        $value = $this->data['cUF'] ?? null;
        return $value === null ? null : (string) $value;
    }

    public function getReceiptDate(): ?string
    {
        $value = $this->data['dhRecbto'] ?? null;
        return $value === null ? null : (string) $value;
    }
}

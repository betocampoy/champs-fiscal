<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Query\Builder;

final class DceQueryPayload
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly array $data
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAccessKey(): string
    {
        return $this->data['accessKey'];
    }

    public function getEnvironment(): string
    {
        return $this->data['environment'];
    }

    public function getVersion(): string
    {
        return $this->data['version'];
    }

    public function getService(): string
    {
        return $this->data['service'];
    }
}

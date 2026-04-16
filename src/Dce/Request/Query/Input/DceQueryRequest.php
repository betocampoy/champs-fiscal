<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Query\Input;

final class DceQueryRequest
{
    public function __construct(
        private ?string $accessKey = null,
        private ?string $environment = null,
        private ?string $version = null,
        private ?string $service = null,
    ) {}

    public function getAccessKey(): ?string { return $this->accessKey; }
    public function setAccessKey(?string $value): void { $this->accessKey = $value; }

    public function getEnvironment(): ?string { return $this->environment; }
    public function setEnvironment(?string $value): void { $this->environment = $value; }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): self
    {
        $this->service = $service;
        return $this;
    }
}

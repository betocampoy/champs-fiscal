<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Builder;

final class DceAuthorizationPayload
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
    public function getIde(): array
    {
        return $this->data['ide'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getEmit(): array
    {
        return $this->data['emit'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDest(): array
    {
        return $this->data['dest'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getDet(): array
    {
        return $this->data['det'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTotal(): array
    {
        return $this->data['total'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTransp(): array
    {
        return $this->data['transp'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTransportadora(): array
    {
        return $this->data['Transportadora'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAutXml(): array
    {
        return $this->data['autXML'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getInfAdic(): array
    {
        return $this->data['infAdic'] ?? [];
    }

    public function hasTransportadora(): bool
    {
        return $this->getTransportadora() !== [];
    }

    public function hasAutXml(): bool
    {
        return $this->getAutXml() !== [];
    }

    public function hasInfAdic(): bool
    {
        return $this->getInfAdic() !== [];
    }
}

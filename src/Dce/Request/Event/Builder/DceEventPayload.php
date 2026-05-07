<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Event\Builder;

final class DceEventPayload
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

    public function getAccessKey(): string
    {
        return (string) $this->data['accessKey'];
    }

    public function getEnvironment(): string
    {
        return (string) $this->data['environment'];
    }

    public function getVersion(): string
    {
        return (string) $this->data['version'];
    }

    public function getService(): string
    {
        return (string) $this->data['service'];
    }

    public function getEventVersion(): string
    {
        return (string) $this->data['eventVersion'];
    }

    public function getEventType(): string
    {
        return (string) $this->data['eventType'];
    }

    public function getSequence(): string
    {
        return (string) $this->data['sequence'];
    }

    public function getEventDate(): string
    {
        return (string) $this->data['eventDate'];
    }

    public function getAuthorDocument(): string
    {
        return (string) $this->data['authorDocument'];
    }

    public function getJustification(): string
    {
        return (string) $this->data['justification'];
    }
}

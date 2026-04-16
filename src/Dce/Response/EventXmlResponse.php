<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Response;

final class EventXmlResponse extends XmlResponse
{
    /**
     * @return array<string, mixed>
     */
    public function getEventInfo(): array
    {
        $value = $this->data['infEvento'] ?? null;

        return is_array($value) ? $value : [];
    }

    public function getStatusCode(): ?string
    {
        $value = $this->getEventInfo()['cStat'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getStatusMessage(): ?string
    {
        $value = $this->getEventInfo()['xMotivo'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getEnvironmentType(): ?string
    {
        $value = $this->getEventInfo()['tpAmb'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getApplicationVersion(): ?string
    {
        $value = $this->getEventInfo()['verAplic'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getAuthorityCode(): ?string
    {
        $value = $this->getEventInfo()['cOrgao'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getAccessKey(): ?string
    {
        $value = $this->getEventInfo()['chDCe'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getEventType(): ?string
    {
        $value = $this->getEventInfo()['tpEvento'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getEventDescription(): ?string
    {
        $value = $this->getEventInfo()['xEvento'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getEventSequence(): ?string
    {
        $value = $this->getEventInfo()['nSeqEvento'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getEventRegistrationDate(): ?string
    {
        $value = $this->getEventInfo()['dhRegEvento'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getProtocolNumber(): ?string
    {
        $value = $this->getEventInfo()['nProt'] ?? null;

        return $value === null ? null : (string) $value;
    }
}

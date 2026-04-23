<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Response;

final class QueryXmlResponse extends XmlResponse
{
    public function getEnvironmentType(): ?string
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

    public function getAccessKey(): ?string
    {
        if (isset($this->data['chDCe'])) {
            return (string) $this->data['chDCe'];
        }

        if (isset($this->data['protDCe']['infProt']['chDCe'])) {
            return (string) $this->data['protDCe']['infProt']['chDCe'];
        }

        if (isset($this->data['procEventoDCe']['retEventoDCe']['infEvento']['chDCe'])) {
            return (string) $this->data['procEventoDCe']['retEventoDCe']['infEvento']['chDCe'];
        }

        return null;
    }

    public function getReceiptDate(): ?string
    {
        if (isset($this->data['dhRecbto'])) {
            return (string) $this->data['dhRecbto'];
        }

        if (isset($this->data['protDCe']['infProt']['dhRecbto'])) {
            return (string) $this->data['protDCe']['infProt']['dhRecbto'];
        }

        return null;
    }

    public function getProtocolNumber(): ?string
    {
        if (isset($this->data['protDCe']['infProt']['nProt'])) {
            return (string) $this->data['protDCe']['infProt']['nProt'];
        }

        return null;
    }

    public function getDigestValue(): ?string
    {
        if (isset($this->data['protDCe']['infProt']['digVal'])) {
            return (string) $this->data['protDCe']['infProt']['digVal'];
        }

        return null;
    }

    public function getAuthorizationStatusCode(): ?string
    {
        if (isset($this->data['protDCe']['infProt']['cStat'])) {
            return (string) $this->data['protDCe']['infProt']['cStat'];
        }

        return null;
    }

    public function getAuthorizationStatusMessage(): ?string
    {
        if (isset($this->data['protDCe']['infProt']['xMotivo'])) {
            return (string) $this->data['protDCe']['infProt']['xMotivo'];
        }

        return null;
    }

    public function hasEventProcess(): bool
    {
        return isset($this->data['procEventoDCe']);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getEventProcess(): ?array
    {
        $value = $this->data['procEventoDCe'] ?? null;

        return is_array($value) ? $value : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getEventInfo(): ?array
    {
        $value = $this->data['procEventoDCe']['retEventoDCe']['infEvento'] ?? null;

        return is_array($value) ? $value : null;
    }

    public function getEventStatusCode(): ?string
    {
        $value = $this->data['procEventoDCe']['retEventoDCe']['infEvento']['cStat'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getEventStatusMessage(): ?string
    {
        $value = $this->data['procEventoDCe']['retEventoDCe']['infEvento']['xMotivo'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getEventProtocolNumber(): ?string
    {
        $value = $this->data['procEventoDCe']['retEventoDCe']['infEvento']['nProt'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getEventType(): ?string
    {
        $value = $this->data['procEventoDCe']['retEventoDCe']['infEvento']['tpEvento'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getEventDescription(): ?string
    {
        $value = $this->data['procEventoDCe']['retEventoDCe']['infEvento']['xEvento'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getEventRegistrationDate(): ?string
    {
        $value = $this->data['procEventoDCe']['retEventoDCe']['infEvento']['dhRegEvento'] ?? null;

        return $value === null ? null : (string) $value;
    }

    public function getProtocolReceiptDate(): ?string
    {
        $value = $this->data['protDCe']['infProt']['dhRecbto'] ?? null;
        return $value === null ? null : (string) $value;
    }

    public function getProtocolStatusCode(): ?string
    {
        $value = $this->data['protDCe']['infProt']['cStat'] ?? null;
        return $value === null ? null : (string) $value;
    }

    public function getProtocolStatusMessage(): ?string
    {
        $value = $this->data['protDCe']['infProt']['xMotivo'] ?? null;
        return $value === null ? null : (string) $value;
    }

    public function isAuthorized(): bool
    {
        return $this->getStatusCode() === '100';
    }
}

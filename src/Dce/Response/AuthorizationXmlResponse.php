<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Response;

final class AuthorizationXmlResponse extends XmlResponse
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

    public function getProtocolNumber(): ?string
    {
        if (isset($this->data['protDCe']['infProt']['nProt'])) {
            return (string) $this->data['protDCe']['infProt']['nProt'];
        }

        $message = $this->getStatusMessage();

        if ($message && preg_match('/\[nProt:\s*([0-9]+)\]/', $message, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function getAccessKey(): ?string
    {
        if (isset($this->data['protDCe']['infProt']['chDCe'])) {
            return (string) $this->data['protDCe']['infProt']['chDCe'];
        }

        if (isset($this->data['chDCe'])) {
            return (string) $this->data['chDCe'];
        }

        return null;
    }

    public function getReceiptDate(): ?string
    {
        if (isset($this->data['protDCe']['infProt']['dhRecbto'])) {
            return (string) $this->data['protDCe']['infProt']['dhRecbto'];
        }

        if (isset($this->data['dhRecbto'])) {
            return (string) $this->data['dhRecbto'];
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
}

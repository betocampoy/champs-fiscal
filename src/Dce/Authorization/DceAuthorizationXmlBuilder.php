<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Authorization;

use XMLWriter;

final class DceAuthorizationXmlBuilder
{
    private const NS = 'http://www.portalfiscal.inf.br/dce';

    public function build(DceAuthorizationData $data): string
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('DCe');
        $xml->writeAttribute('xmlns', self::NS);

        $xml->startElement('infDCe');
        $xml->writeAttribute('versao', $data->versao);
        $xml->writeAttribute('Id', 'DCe' . $data->accessKey);

        $this->buildIde($xml, $data);
        $this->buildEmit($xml, $data);
        $this->buildActorGroup($xml, $data);
        $this->buildDest($xml, $data);
        $this->buildAutXml($xml, $data);
        $this->buildItems($xml, $data);
        $this->buildTotal($xml, $data);
        $this->buildTransp($xml, $data);
        $this->buildInfAdic($xml, $data);
        $this->buildInfDec($xml, $data);

        $xml->endElement(); // infDCe

        $this->buildInfSolicDce($xml, $data);
        $this->buildInfDceSupl($xml, $data);

        $xml->endElement(); // DCe
        $xml->endDocument();

        return $xml->outputMemory();
    }

    private function buildIde(XMLWriter $xml, DceAuthorizationData $data): void
    {
        $xml->startElement('ide');

        $xml->writeElement('cUF', (string) $data->cUF);
        $xml->writeElement('cDC', $data->cDC);
        $xml->writeElement('mod', str_pad((string) $data->mod, 2, '0', STR_PAD_LEFT));
        $xml->writeElement('serie', (string) $data->serie);
        $xml->writeElement('nDC', (string) $data->nDC);
        $xml->writeElement('dhEmi', $data->dhEmi->format('Y-m-d\TH:i:sP'));
        $xml->writeElement('tpEmis', (string) $data->tpEmis);
        $xml->writeElement('tpEmit', (string) $data->tpEmit);
        $xml->writeElement('nSiteAutoriz', (string) $data->nSiteAutoriz);
        $xml->writeElement('cDV', substr($data->accessKey, -1));
        $xml->writeElement('tpAmb', (string) $data->tpAmb);
        $xml->writeElement('verProc', $data->verProc);

        $xml->endElement();
    }

    private function buildEmit(XMLWriter $xml, DceAuthorizationData $data): void
    {
        $xml->startElement('emit');

        $this->writeChoiceDocument($xml, $data->emitCnpj, $data->emitCpf, $data->emitIdOutros);
        $xml->writeElement('xNome', $data->emitNome);

        $xml->startElement('enderEmit');
        $this->writeAddress($xml, $data->emitEndereco);
        $xml->endElement();

        $xml->endElement();
    }

    private function buildActorGroup(XMLWriter $xml, DceAuthorizationData $data): void
    {
        if ($data->fisco !== null) {
            $xml->startElement('Fisco');
            $xml->writeElement('CNPJ', $data->fisco['cnpj']);
            $xml->writeElement('xOrgao', $data->fisco['xOrgao']);
            $xml->writeElement('UF', $data->fisco['uf']);
            $xml->endElement();
            return;
        }

        if ($data->marketplace !== null) {
            $xml->startElement('Marketplace');
            $xml->writeElement('CNPJ', $data->marketplace['cnpj']);
            $xml->writeElement('xNome', $data->marketplace['xNome']);
            $xml->writeElement('Site', $data->marketplace['site']);
            $xml->endElement();
            return;
        }

        if ($data->transportadoraEmissora !== null) {
            $xml->startElement('Transportadora');
            $xml->writeElement('CNPJ', $data->transportadoraEmissora['cnpj']);
            $xml->writeElement('xNome', $data->transportadoraEmissora['xNome']);
            $xml->endElement();
            return;
        }

        if ($data->emissaoPropria !== null) {
            $xml->startElement('EmpEmisProp');
            $xml->writeElement('CNPJ', $data->emissaoPropria['cnpj']);
            $xml->writeElement('xNome', $data->emissaoPropria['xNome']);
            $xml->endElement();
        }
    }

    private function buildDest(XMLWriter $xml, DceAuthorizationData $data): void
    {
        $xml->startElement('dest');

        $this->writeChoiceDocument($xml, $data->destCnpj, $data->destCpf, $data->destIdOutros);

        if ($this->hasValue($data->destNome)) {
            $xml->writeElement('xNome', $data->destNome);
        }

        $xml->startElement('enderDest');
        $this->writeAddress($xml, $data->destEndereco, $data->destEmail);
        $xml->endElement();

        $xml->endElement();
    }

    private function buildAutXml(XMLWriter $xml, DceAuthorizationData $data): void
    {
        foreach ($data->autXml as $authorized) {
            $xml->startElement('autXML');

            if ($authorized['type'] === 'CNPJ') {
                $xml->writeElement('CNPJ', $authorized['document']);
            } elseif ($authorized['type'] === 'CPF') {
                $xml->writeElement('CPF', $authorized['document']);
            }

            $xml->endElement();
        }
    }

    private function buildItems(XMLWriter $xml, DceAuthorizationData $data): void
    {
        foreach ($data->items as $index => $item) {
            $xml->startElement('det');
            $xml->writeAttribute('nItem', (string) ($index + 1));

            $xml->startElement('prod');
            $xml->writeElement('xProd', $item->xProd);

            if ($this->hasValue($item->ncm)) {
                $xml->writeElement('NCM', $item->ncm);
            }

            $xml->writeElement('qCom', $this->formatDecimal($item->qCom, 4));
            $xml->writeElement('vUnCom', $this->formatDecimal($item->vUnCom, 8));
            $xml->writeElement('vProd', $this->formatDecimal($item->vProd, 2));
            $xml->endElement();

            if ($this->hasValue($item->infAdProd)) {
                $xml->writeElement('infAdProd', $item->infAdProd);
            }

            $xml->endElement();
        }
    }

    private function buildTotal(XMLWriter $xml, DceAuthorizationData $data): void
    {
        $xml->startElement('total');
        $xml->writeElement('vDC', $this->formatDecimal($data->vDC, 2));
        $xml->endElement();
    }

    private function buildTransp(XMLWriter $xml, DceAuthorizationData $data): void
    {
        $xml->startElement('transp');
        $xml->writeElement('modTrans', (string) $data->modTrans);

        if ($this->hasValue($data->cnpjTransp)) {
            $xml->writeElement('CNPJTransp', $data->cnpjTransp);
        }

        $xml->endElement();
    }

    private function buildInfAdic(XMLWriter $xml, DceAuthorizationData $data): void
    {
        if (
            !$this->hasValue($data->infAdFisco) &&
            !$this->hasValue($data->infCpl) &&
            !$this->hasValue($data->infAdMarketplace)
        ) {
            return;
        }

        $xml->startElement('infAdic');

        if ($this->hasValue($data->infAdFisco)) {
            $xml->writeElement('infAdFisco', $data->infAdFisco);
        }

        if ($this->hasValue($data->infCpl)) {
            $xml->writeElement('infCpl', $data->infCpl);
        }

        if ($this->hasValue($data->infAdMarketplace)) {
            $xml->writeElement('infAdMarketplace', $data->infAdMarketplace);
        }

        $xml->endElement();
    }

    private function buildInfDec(XMLWriter $xml, DceAuthorizationData $data): void
    {
        $xml->startElement('infDec');
        $xml->writeElement('xObs1', $data->xObs1);
        $xml->writeElement('xObs2', $data->xObs2);
        $xml->endElement();
    }

    private function buildInfSolicDce(XMLWriter $xml, DceAuthorizationData $data): void
    {
        $xml->startElement('infSolicDCe');
        $xml->writeElement('xSolic', $data->xSolic ?? '');
        $xml->endElement();
    }

    private function buildInfDceSupl(XMLWriter $xml, DceAuthorizationData $data): void
    {
        $xml->startElement('infDCeSupl');

        $xml->startElement('qrCodDCe');
        $this->writeCdata($xml, $data->qrCode);
        $xml->endElement();

        $xml->writeElement('urlChave', $data->urlChave);

        $xml->endElement();
    }

    private function writeChoiceDocument(XMLWriter $xml, ?string $cnpj, ?string $cpf, ?string $idOutros): void
    {
        if ($this->hasValue($cnpj)) {
            $xml->writeElement('CNPJ', $cnpj);
            return;
        }

        if ($this->hasValue($cpf)) {
            $xml->writeElement('CPF', $cpf);
            return;
        }

        if ($this->hasValue($idOutros)) {
            $xml->writeElement('idOutros', $idOutros);
        }
    }

    /**
     * @param array<string, string|null> $address
     */
    private function writeAddress(XMLWriter $xml, array $address, ?string $email = null): void
    {
        $xml->writeElement('xLgr', (string) $address['xLgr']);
        $xml->writeElement('nro', (string) $address['nro']);

        if ($this->hasValue($address['xCpl'] ?? null)) {
            $xml->writeElement('xCpl', (string) $address['xCpl']);
        }

        $xml->writeElement('xBairro', (string) $address['xBairro']);
        $xml->writeElement('cMun', (string) $address['cMun']);
        $xml->writeElement('xMun', (string) $address['xMun']);
        $xml->writeElement('UF', (string) $address['UF']);

        if ($this->hasValue($address['CEP'] ?? null)) {
            $xml->writeElement('CEP', (string) $address['CEP']);
        }

        $xml->writeElement('cPais', (string) $address['cPais']);
        $xml->writeElement('xPais', (string) $address['xPais']);

        if ($this->hasValue($address['fone'] ?? null)) {
            $xml->writeElement('fone', (string) $address['fone']);
        }

        if ($this->hasValue($email)) {
            $xml->writeElement('email', (string) $email);
        }
    }

    private function writeCdata(XMLWriter $xml, string $value): void
    {
        $xml->writeRaw('<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $value) . ']]>');
    }

    private function hasValue(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }

    private function formatDecimal(float|int|string $value, int $scale): string
    {
        return number_format((float) $value, $scale, '.', '');
    }
}

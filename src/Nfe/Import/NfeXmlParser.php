<?php

namespace BetoCampoy\Champs\Fiscal\Nfe\Import;

use BetoCampoy\Champs\Fiscal\Nfe\Import\Dto\ImportedNfeData;
use BetoCampoy\Champs\Fiscal\Nfe\Import\Dto\NfeAddressData;
use BetoCampoy\Champs\Fiscal\Nfe\Import\Dto\NfePartyData;
use BetoCampoy\Champs\Fiscal\Nfe\Import\Dto\NfeVolumeData;

final class NfeXmlParser
{
    public function parse(string $xmlContent): ImportedNfeData
    {
        $xml = $this->loadXml($xmlContent);

        $infNfe = $this->first($xml, '//*[local-name()="infNFe"]');

        if (!$infNfe instanceof \DOMElement) {
            throw new \RuntimeException('XML não possui a tag infNFe.');
        }

        $ide = $this->first($infNfe, './*[local-name()="ide"]');
        $emit = $this->first($infNfe, './*[local-name()="emit"]');
        $dest = $this->first($infNfe, './*[local-name()="dest"]');
        $total = $this->first($infNfe, './*[local-name()="total"]/*[local-name()="ICMSTot"]');
        $transp = $this->first($infNfe, './*[local-name()="transp"]');
        $entrega = $this->first($infNfe, './*[local-name()="entrega"]');

        if (!$ide instanceof \DOMElement) {
            throw new \RuntimeException('XML da NF-e sem grupo ide.');
        }

        if (!$emit instanceof \DOMElement) {
            throw new \RuntimeException('XML da NF-e sem grupo emit.');
        }

        if (!$dest instanceof \DOMElement) {
            throw new \RuntimeException('XML da NF-e sem grupo dest.');
        }

        $accessKey = $this->extractAccessKey($infNfe);

        $billingAddress = $this->parseAddress(
            $this->first($dest, './*[local-name()="enderDest"]')
        );

        $deliveryAddress = $entrega instanceof \DOMElement
            ? $this->parseDeliveryAddress($entrega, $billingAddress)
            : $billingAddress;

        $volumes = $this->parseVolumes($transp);
        $packageCount = $this->resolvePackageCount($volumes);
        $totalWeightGrams = $this->resolveTotalWeightGrams($volumes);

        return new ImportedNfeData(
            accessKey: $accessKey,
            environment: $this->text($ide, 'tpAmb'),
            state: $this->text($ide, 'cUF'),
            stateCode: $this->text($ide, 'cUF'),
            series: (string) $this->text($ide, 'serie'),
            number: (string) $this->text($ide, 'nNF'),
            issuedAt: $this->normalizeDateTime((string) ($this->text($ide, 'dhEmi') ?: $this->text($ide, 'dEmi'))),

            emitter: $this->parseParty($emit, 'emit'),
            emitterAddress: $this->parseAddress(
                $this->first($emit, './*[local-name()="enderEmit"]')
            ),

            recipient: $this->parseParty($dest, 'dest'),
            billingAddress: $billingAddress,
            deliveryAddress: $deliveryAddress,

            transporter: $this->parseTransporter($transp),

            totalValue: $this->toFloat($this->text($total, 'vNF')) ?? 0.0,
            freightMode: $this->text($transp, 'modFrete'),
            freightValue: $this->toFloat($this->text($total, 'vFrete')),

            packageCount: $packageCount,
            totalWeightGrams: $totalWeightGrams,

            items: $this->parseItems($infNfe),
            volumes: $volumes,
            details: $this->parseDetails($infNfe),
            additionalInfo: $this->text(
                $this->first($infNfe, './*[local-name()="infAdic"]'),
                'infCpl'
            ),
        );
    }

    private function loadXml(string $xmlContent): \DOMDocument
    {
        $previous = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;

        if (!$dom->loadXML($xmlContent)) {
            $errors = array_map(
                static fn (\LibXMLError $error): string => trim($error->message),
                libxml_get_errors()
            );

            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            throw new \RuntimeException(
                'XML inválido: ' . implode('; ', array_filter($errors))
            );
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $dom;
    }

    private function extractAccessKey(\DOMElement $infNfe): string
    {
        $id = (string) $infNfe->getAttribute('Id');
        $key = preg_replace('/\D+/', '', $id) ?: '';

        if (strlen($key) !== 44) {
            throw new \RuntimeException('Chave de acesso da NF-e não encontrada ou inválida.');
        }

        return $key;
    }

    private function parseParty(\DOMElement $node, string $type): NfePartyData
    {
        $document = $this->text($node, 'CNPJ') ?: $this->text($node, 'CPF');

        $nameTag = $type === 'emit' ? 'xNome' : 'xNome';

        return new NfePartyData(
            document: $this->onlyDigits($document),
            name: $this->text($node, $nameTag),
            phone: $this->text($node, $type === 'emit' ? 'fone' : 'fone'),
            email: $this->text($node, 'email'),
        );
    }

    private function parseAddress(?\DOMElement $address): NfeAddressData
    {
        if (!$address instanceof \DOMElement) {
            return new NfeAddressData(
                street: null,
                number: null,
                complement: null,
                neighborhood: null,
                cityCode: null,
                city: null,
                state: null,
                zipcode: null,
                countryCode: null,
                country: null,
            );
        }

        return new NfeAddressData(
            street: $this->text($address, 'xLgr'),
            number: $this->text($address, 'nro'),
            complement: $this->text($address, 'xCpl'),
            neighborhood: $this->text($address, 'xBairro'),
            cityCode: $this->text($address, 'cMun'),
            city: $this->text($address, 'xMun'),
            state: $this->text($address, 'UF'),
            zipcode: $this->onlyDigits($this->text($address, 'CEP')),
            countryCode: $this->text($address, 'cPais'),
            country: $this->text($address, 'xPais'),
        );
    }

    private function parseDeliveryAddress(
        \DOMElement $delivery,
        NfeAddressData $fallback
    ): NfeAddressData {
        return new NfeAddressData(
            street: $this->text($delivery, 'xLgr') ?: $fallback->street,
            number: $this->text($delivery, 'nro') ?: $fallback->number,
            complement: $this->text($delivery, 'xCpl') ?: $fallback->complement,
            neighborhood: $this->text($delivery, 'xBairro') ?: $fallback->neighborhood,
            cityCode: $this->text($delivery, 'cMun') ?: $fallback->cityCode,
            city: $this->text($delivery, 'xMun') ?: $fallback->city,
            state: $this->text($delivery, 'UF') ?: $fallback->state,
            zipcode: $this->onlyDigits($this->text($delivery, 'CEP')) ?: $fallback->zipcode,
            countryCode: $this->text($delivery, 'cPais') ?: $fallback->countryCode,
            country: $this->text($delivery, 'xPais') ?: $fallback->country,
        );
    }

    /**
     * @return array<int, NfeVolumeData>
     */
    private function parseVolumes(?\DOMElement $transp): array
    {
        if (!$transp instanceof \DOMElement) {
            return [];
        }

        $volNodes = $this->all($transp, './*[local-name()="vol"]');
        $volumes = [];

        foreach ($volNodes as $vol) {
            if (!$vol instanceof \DOMElement) {
                continue;
            }

            $volumes[] = new NfeVolumeData(
                quantity: $this->toInt($this->text($vol, 'qVol')),
                species: $this->text($vol, 'esp'),
                brand: $this->text($vol, 'marca'),
                numbering: $this->text($vol, 'nVol'),
                grossWeightGrams: $this->kgToGrams($this->toFloat($this->text($vol, 'pesoB'))),
                netWeightGrams: $this->kgToGrams($this->toFloat($this->text($vol, 'pesoL'))),
            );
        }

        return $volumes;
    }

    private function resolvePackageCount(array $volumes): int
    {
        $count = 0;

        foreach ($volumes as $volume) {
            if (!$volume instanceof NfeVolumeData) {
                continue;
            }

            $count += max(0, (int) ($volume->quantity ?? 0));
        }

        return $count;
    }

    private function resolveTotalWeightGrams(array $volumes): int
    {
        $weight = 0;

        foreach ($volumes as $volume) {
            if (!$volume instanceof NfeVolumeData) {
                continue;
            }

            $weight += max(0, (int) ($volume->grossWeightGrams ?? 0));
        }

        return $weight;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseItems(\DOMElement $infNfe): array
    {
        $items = [];

        foreach ($this->all($infNfe, './*[local-name()="det"]') as $det) {
            if (!$det instanceof \DOMElement) {
                continue;
            }

            $prod = $this->first($det, './*[local-name()="prod"]');

            if (!$prod instanceof \DOMElement) {
                continue;
            }

            $items[] = [
                'item_number' => $det->getAttribute('nItem') ?: null,
                'code' => $this->text($prod, 'cProd'),
                'ean' => $this->text($prod, 'cEAN'),
                'description' => $this->text($prod, 'xProd'),
                'ncm' => $this->text($prod, 'NCM'),
                'cfop' => $this->text($prod, 'CFOP'),
                'unit' => $this->text($prod, 'uCom'),
                'quantity' => $this->toFloat($this->text($prod, 'qCom')),
                'unit_value' => $this->toFloat($this->text($prod, 'vUnCom')),
                'total_value' => $this->toFloat($this->text($prod, 'vProd')),
            ];
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseDetails(\DOMElement $infNfe): array
    {
        $ide = $this->first($infNfe, './*[local-name()="ide"]');
        $total = $this->first($infNfe, './*[local-name()="total"]/*[local-name()="ICMSTot"]');
        $transp = $this->first($infNfe, './*[local-name()="transp"]');

        return [
            'ide' => [
                'nat_op' => $this->text($ide, 'natOp'),
                'model' => $this->text($ide, 'mod'),
                'tp_nf' => $this->text($ide, 'tpNF'),
                'id_dest' => $this->text($ide, 'idDest'),
                'municipality_code' => $this->text($ide, 'cMunFG'),
                'emission_type' => $this->text($ide, 'tpEmis'),
                'purpose' => $this->text($ide, 'finNFe'),
                'consumer_final' => $this->text($ide, 'indFinal'),
            ],
            'totals' => [
                'products_value' => $this->toFloat($this->text($total, 'vProd')),
                'invoice_value' => $this->toFloat($this->text($total, 'vNF')),
                'freight_value' => $this->toFloat($this->text($total, 'vFrete')),
                'insurance_value' => $this->toFloat($this->text($total, 'vSeg')),
                'discount_value' => $this->toFloat($this->text($total, 'vDesc')),
                'other_value' => $this->toFloat($this->text($total, 'vOutro')),
            ],
            'transport' => [
                'freight_mode' => $this->text($transp, 'modFrete'),
            ],
        ];
    }

    private function parseTransporter(?\DOMElement $transp): ?NfePartyData
    {
        if (!$transp instanceof \DOMElement) {
            return null;
        }

        $transporta = $this->first($transp, './*[local-name()="transporta"]');

        if (!$transporta instanceof \DOMElement) {
            return null;
        }

        return new NfePartyData(
            document: $this->onlyDigits($this->text($transporta, 'CNPJ') ?: $this->text($transporta, 'CPF')),
            name: $this->text($transporta, 'xNome'),
        );
    }

    private function normalizeDateTime(string $value): string
    {
        if ($value === '') {
            throw new \RuntimeException('Data de emissão da NF-e não encontrada.');
        }

        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            throw new \RuntimeException('Data de emissão da NF-e inválida.');
        }
    }

    private function text(?\DOMNode $context, string $tag): ?string
    {
        if (!$context instanceof \DOMNode) {
            return null;
        }

        $node = $this->first($context, './*[local-name()="' . $tag . '"]');

        if (!$node instanceof \DOMNode) {
            return null;
        }

        $value = trim($node->textContent);

        return $value !== '' ? $value : null;
    }

    private function first(\DOMNode $context, string $xpath): ?\DOMNode
    {
        $query = new \DOMXPath($context instanceof \DOMDocument ? $context : $context->ownerDocument);

        $nodes = $query->query($xpath, $context);

        return $nodes && $nodes->length > 0 ? $nodes->item(0) : null;
    }

    /**
     * @return \DOMNodeList<\DOMNode>
     */
    private function all(\DOMNode $context, string $xpath): \DOMNodeList
    {
        $query = new \DOMXPath($context instanceof \DOMDocument ? $context : $context->ownerDocument);

        return $query->query($xpath, $context);
    }

    private function onlyDigits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        return $digits !== '' ? $digits : null;
    }

    private function toFloat(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) str_replace(',', '.', $value);
    }

    private function toInt(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function kgToGrams(?float $kg): ?int
    {
        if ($kg === null) {
            return null;
        }

        return (int) round($kg * 1000);
    }
}

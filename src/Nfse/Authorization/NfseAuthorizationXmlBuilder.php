<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Authorization;

use DOMDocument;
use DOMElement;

final class NfseAuthorizationXmlBuilder
{
    private const NS = 'http://www.sped.fazenda.gov.br/nfse';
    private const VERSAO = '1.00';

    private DOMDocument $dom;

    public function build(NfseAuthorizationData $data): string
    {
        if (class_exists(NfseAuthorizationDataValidator::class)) {
            (new NfseAuthorizationDataValidator())->validate($data);
        }

        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = false;

        $root = $this->dom->createElementNS(self::NS, 'DPS');
        $root->setAttribute('versao', self::VERSAO);
        $this->dom->appendChild($root);

        $infDps = $this->dom->createElement('infDPS');
        $infDps->setAttribute('Id', $data->getDpsId());
        $root->appendChild($infDps);

        $this->buildInfDps($infDps, $data);

        $xml = $this->dom->saveXML($root);

        return str_replace(["\n", "\r", "\t"], '', $xml);
    }

    private function buildInfDps(DOMElement $parent, NfseAuthorizationData $data): void
    {
        $p = $data->provider;
        $t = $data->taker;
        $s = $data->service;
        $v = $data->values;

        $opSimpNac = $this->readString($p, ['opSimpNac', 'simplesNacionalOption']);
        $regApTribSN = $this->readString($p, ['regApTribSN', 'simplesNacionalRegime']);
        $regEspTrib = $this->readString($p, ['regEspTrib', 'specialTaxRegime']);

        if ($opSimpNac === null || $opSimpNac === '') {
            $opSimpNac = $p->simplesNacional ? '3' : '2';
        }

        if (($regApTribSN === null || $regApTribSN === '') && $p->simplesNacional) {
            $regApTribSN = '2';
        }

        if ($regEspTrib === null || $regEspTrib === '') {
            $regEspTrib = '0';
        }

        $isSimplesNacional = in_array($opSimpNac, ['1', '3'], true);

        $this->el($parent, 'tpAmb', (string) $data->environment);
        $this->el($parent, 'dhEmi', $data->emissionDate->format('Y-m-d\TH:i:sP'));
        $this->el($parent, 'verAplic', $data->applicationVersion);
        $this->el($parent, 'serie', $this->normalizeSeries($data->rpsSeries));
        $this->el($parent, 'nDPS', (string) $data->rpsNumber);
        $this->el($parent, 'dCompet', $data->competenceDate);
        $this->el($parent, 'tpEmit', '1');
        $this->el($parent, 'cLocEmi', $this->onlyDigits($p->emitterIbgeCode));

        $prest = $this->dom->createElement('prest');
        $this->el($prest, 'CNPJ', $this->onlyDigits($p->cnpj));
        $this->el($prest, 'CPF', $this->onlyDigits($p->cpf));

        if ($p->municipalRegistration !== '') {
            $this->el($prest, 'IM', $p->municipalRegistration);
        }

        $this->el($prest, 'fone', $this->onlyDigits($this->readString($p, ['phone', 'fone'])));
        $this->el($prest, 'email', $this->readString($p, ['email']));

        $regTrib = $this->dom->createElement('regTrib');
        $this->el($regTrib, 'opSimpNac', $opSimpNac);

        if ($isSimplesNacional && $regApTribSN !== null && $regApTribSN !== '') {
            $this->el($regTrib, 'regApTribSN', $regApTribSN);
        }

        $this->el($regTrib, 'regEspTrib', $regEspTrib);
        $prest->appendChild($regTrib);
        $parent->appendChild($prest);

        $toma = $this->dom->createElement('toma');
        $this->el($toma, 'CNPJ', $this->onlyDigits($t->cnpj));
        $this->el($toma, 'CPF', $this->onlyDigits($t->cpf));
        $this->el($toma, 'NIF', $t->foreignId);
        $this->el($toma, 'xNome', $t->name);

        if ($t->address !== null) {
            $end = $this->dom->createElement('end');

            $endNac = $this->dom->createElement('endNac');
            $this->el($endNac, 'cMun', $this->onlyDigits($t->address->ibgeCode));
            $this->el(
                $endNac,
                'CEP',
                str_pad(
                    $this->onlyDigits($t->address->zipCode),
                    8,
                    '0',
                    STR_PAD_LEFT
                )
            );
            $end->appendChild($endNac);

            $this->el($end, 'xLgr', $t->address->street);
            $this->el($end, 'nro', $t->address->number);
            $this->el($end, 'xCpl', $t->address->complement);
            $this->el($end, 'xBairro', $t->address->neighborhood);

            $toma->appendChild($end);
        }

        $this->el($toma, 'fone', $this->onlyDigits($t->phone));
        $this->el($toma, 'email', $t->email);
        $parent->appendChild($toma);

        $serv = $this->dom->createElement('serv');

        $locPrest = $this->dom->createElement('locPrest');
        $this->el($locPrest, 'cLocPrestacao', $this->onlyDigits($s->serviceMunicipalityIbge));
        $serv->appendChild($locPrest);

        $cServ = $this->dom->createElement('cServ');
        $this->el($cServ, 'cTribNac', $this->normalizeNationalServiceCode($s->nationalServiceCode));

        $municipalCode = trim((string) $s->municipalServiceCode);
        if ($municipalCode !== '' && $municipalCode !== '000') {
            $this->el($cServ, 'cTribMun', $municipalCode);
        }

        $this->el($cServ, 'xDescServ', $s->description);

        $nbs = $this->readString($s, ['nbsCode', 'cNBS', 'cNbs', 'nbs']);
        if ($nbs !== null && trim($nbs) !== '') {
            $this->el($cServ, 'cNBS', $this->onlyDigits($nbs));
        }

        $serv->appendChild($cServ);

        if ($data->additionalInfo !== null) {
            $infoCompl = $this->dom->createElement('infoCompl');
            $this->el($infoCompl, 'xInfComp', $data->additionalInfo);
            $serv->appendChild($infoCompl);
        }

        $parent->appendChild($serv);

        $valores = $this->dom->createElement('valores');

        $vServPrest = $this->dom->createElement('vServPrest');

        if ($v->getNetValue() !== $v->serviceValue) {
            $this->el($vServPrest, 'vReceb', $this->fmt($v->getNetValue()));
        }

        $this->el($vServPrest, 'vServ', $this->fmt($v->serviceValue));
        $valores->appendChild($vServPrest);

        if ($v->unconditionalDiscount > 0 || $v->conditionalDiscount > 0) {
            $vDesc = $this->dom->createElement('vDescCondIncond');

            if ($v->unconditionalDiscount > 0) {
                $this->el($vDesc, 'vDescIncond', $this->fmt($v->unconditionalDiscount));
            }

            if ($v->conditionalDiscount > 0) {
                $this->el($vDesc, 'vDescCond', $this->fmt($v->conditionalDiscount));
            }

            $valores->appendChild($vDesc);
        }

        $trib = $this->dom->createElement('trib');

        $tribMun = $this->dom->createElement('tribMun');
        $this->el($tribMun, 'tribISSQN', '1');
        $this->el($tribMun, 'tpRetISSQN', $v->issRetained ? '2' : '1');

        if (!$isSimplesNacional && $v->issAliquot > 0) {
            $this->el($tribMun, 'pAliq', $this->fmt($v->issAliquot * 100, 2));
        }

        $trib->appendChild($tribMun);

        $tribFed = $this->dom->createElement('tribFed');

        $pisCofinsCst = $this->readString($v, ['pisCofinsCst', 'pisCofinsCST', 'cstPisCofins']);
        if ($pisCofinsCst !== null && trim($pisCofinsCst) !== '') {
            $piscofins = $this->dom->createElement('piscofins');
            $this->el($piscofins, 'CST', $pisCofinsCst);
            $tribFed->appendChild($piscofins);
        }

        $this->el($tribFed, 'vRetIRRF', $v->irValue !== null ? $this->fmt($v->irValue) : null);
        $this->el($tribFed, 'vRetCSLL', $v->csllValue !== null ? $this->fmt($v->csllValue) : null);
        $this->el($tribFed, 'vRetContPrev', $v->inssValue !== null ? $this->fmt($v->inssValue) : null);

        if ($tribFed->hasChildNodes()) {
            $trib->appendChild($tribFed);
        }

        $totTrib = $this->dom->createElement('totTrib');

        $totalTaxPercent = $this->readFloat($v, [
            'totalTaxPercent',
            'approxTaxPercent',
            'pTotTribSN',
            'simplesTotalTaxPercent',
        ]);

        if ($isSimplesNacional) {
            if ($totalTaxPercent === null && $v->issAliquot > 0) {
                $totalTaxPercent = $v->issAliquot * 100;
            }

            if ($totalTaxPercent !== null && $totalTaxPercent > 0) {
                $this->el($totTrib, 'pTotTribSN', $this->fmt($totalTaxPercent, 2));
            } else {
                $this->el($totTrib, 'indTotTrib', '0');
            }
        } else {
            $this->el($totTrib, 'indTotTrib', '0');
        }

        $trib->appendChild($totTrib);

        $valores->appendChild($trib);
        $parent->appendChild($valores);
    }

    private function el(DOMElement $parent, string $name, ?string $value): void
    {
        if ($value !== null && $value !== '') {
            $node = $this->dom->createElement($name);
            $node->appendChild($this->dom->createTextNode($value));
            $parent->appendChild($node);
        }
    }

    private function fmt(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, '.', '');
    }

    private function normalizeSeries(string $series): string
    {
        $digits = $this->onlyDigits($series);

        return str_pad($digits !== '' ? $digits : '1', 5, '0', STR_PAD_LEFT);
    }

    private function normalizeNationalServiceCode(string $code): string
    {
        $digits = $this->onlyDigits($code);

        if (strlen($digits) === 3) {
            return '0' . substr($digits, 0, 1) . '0' . substr($digits, 1);
        }

        return str_pad($digits, 6, '0', STR_PAD_LEFT);
    }

    private function onlyDigits(?string $value): string
    {
        return preg_replace('/\D/', '', (string) $value);
    }

    private function readString(object $object, array $names): ?string
    {
        foreach ($names as $name) {
            if (property_exists($object, $name) && $object->{$name} !== null) {
                return (string) $object->{$name};
            }

            $method = 'get' . ucfirst($name);
            if (method_exists($object, $method)) {
                $value = $object->{$method}();
                if ($value !== null) {
                    return (string) $value;
                }
            }
        }

        return null;
    }

    private function readFloat(object $object, array $names): ?float
    {
        foreach ($names as $name) {
            if (property_exists($object, $name) && $object->{$name} !== null) {
                return (float) $object->{$name};
            }

            $method = 'get' . ucfirst($name);
            if (method_exists($object, $method)) {
                $value = $object->{$method}();
                if ($value !== null) {
                    return (float) $value;
                }
            }
        }

        return null;
    }
}

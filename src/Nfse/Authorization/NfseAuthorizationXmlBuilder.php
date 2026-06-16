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

        $this->el($parent, 'tpAmb', (string) $data->environment);
        $this->el($parent, 'dhEmi', $data->emissionDate->format('Y-m-d\TH:i:sP'));
        $this->el($parent, 'verAplic', $data->applicationVersion);
        $this->el($parent, 'serie', $data->rpsSeries);
        $this->el($parent, 'nDPS', (string) $data->rpsNumber);
        $this->el($parent, 'dCompet', $data->competenceDate);
        $this->el($parent, 'tpEmit', '1');
        $this->el($parent, 'cLocEmi', $p->emitterIbgeCode);

        // prest
        $prest = $this->dom->createElement('prest');
        $this->el($prest, 'CNPJ', $p->cnpj);
        $this->el($prest, 'CPF', $p->cpf);
        if ($p->municipalRegistration !== '') {
            $this->el($prest, 'IM', $p->municipalRegistration);
        }

        $regTrib = $this->dom->createElement('regTrib');
        $this->el($regTrib, 'opSimpNac', $p->simplesNacional ? '1' : '2');
        if ($p->simplesNacional) {
            $this->el($regTrib, 'regApTribSN', '1');
        }
        $this->el($regTrib, 'regEspTrib', '0');
        $prest->appendChild($regTrib);
        $parent->appendChild($prest);

        // toma
        $toma = $this->dom->createElement('toma');
        $this->el($toma, 'CNPJ', $t->cnpj);
        $this->el($toma, 'CPF', $t->cpf);
        $this->el($toma, 'NIF', $t->foreignId);
        $this->el($toma, 'xNome', $t->name);

        if ($t->address !== null) {
            $endNac = $this->dom->createElement('endNac');
            $this->el($endNac, 'cMun', $t->address->ibgeCode);
            $this->el($endNac, 'CEP', $t->address->zipCode);
            $this->el($endNac, 'xLgr', $t->address->street);
            $this->el($endNac, 'nro', $t->address->number);
            $this->el($endNac, 'xCpl', $t->address->complement);
            $this->el($endNac, 'xBairro', $t->address->neighborhood);
            $end = $this->dom->createElement('end');
            $end->appendChild($endNac);
            $toma->appendChild($end);
        }

        $this->el($toma, 'fone', $t->phone);
        $this->el($toma, 'email', $t->email);
        $parent->appendChild($toma);

        // serv — atenção: locPrest vem antes de cServ
        $serv = $this->dom->createElement('serv');

        $locPrest = $this->dom->createElement('locPrest');
        $this->el($locPrest, 'cLocPrestacao', $s->serviceMunicipalityIbge);
        $serv->appendChild($locPrest);

        $cServ = $this->dom->createElement('cServ');
        $this->el($cServ, 'cTribNac', $s->nationalServiceCode);
        $this->el($cServ, 'cTribMun', $s->municipalServiceCode);
        $this->el($cServ, 'xDescServ', $s->description);
        if ($s->cnae !== null) {
            $this->el($cServ, 'CNAE', $s->cnae);
        }
        $serv->appendChild($cServ);

        if ($data->additionalInfo !== null) {
            $infoCompl = $this->dom->createElement('infoCompl');
            $this->el($infoCompl, 'xInfComp', $data->additionalInfo);
            $serv->appendChild($infoCompl);
        }

        $parent->appendChild($serv);

        // valores
        $valores = $this->dom->createElement('valores');

        $vServPrest = $this->dom->createElement('vServPrest');
        $this->el($vServPrest, 'vReceb', $this->fmt($v->getNetValue()));
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
        $this->el($tribMun, 'cLocIncid', $v->issIncidenceIbge);
        $this->el($tribMun, 'tpRetISSQN', $v->issRetained ? '1' : '2');
        $this->el($tribMun, 'pAliq', $this->fmt($v->issAliquot * 100, 2));
        $trib->appendChild($tribMun);

        $hasTribFed = $v->inssValue !== null || $v->irValue !== null || $v->csllValue !== null;
        if ($hasTribFed) {
            $tribFed = $this->dom->createElement('tribFed');
            $this->el($tribFed, 'vRetIRRF', $v->irValue !== null ? $this->fmt($v->irValue) : null);
            $this->el($tribFed, 'vRetCSLL', $v->csllValue !== null ? $this->fmt($v->csllValue) : null);
            $this->el($tribFed, 'vRetContPrev', $v->inssValue !== null ? $this->fmt($v->inssValue) : null);
            $trib->appendChild($tribFed);
        }

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
}

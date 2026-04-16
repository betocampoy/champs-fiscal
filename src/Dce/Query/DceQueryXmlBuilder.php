<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Query;

use XMLWriter;

final class DceQueryXmlBuilder
{
    private const NS = 'http://www.portalfiscal.inf.br/dce';

    public function build(DceQueryData $data): string
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('consSitDCe');
        $xml->writeAttribute('xmlns', self::NS);
        $xml->writeAttribute('versao', $data->versao);

        $xml->writeElement('tpAmb', (string) $data->tpAmb);
        $xml->writeElement('xServ', $data->xServ);
        $xml->writeElement('chDCe', $data->chDCe);

        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }
}

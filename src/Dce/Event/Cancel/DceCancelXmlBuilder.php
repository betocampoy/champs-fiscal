<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Event\Cancel;

use XMLWriter;

final class DceCancelXmlBuilder
{
    private const NS = 'http://www.portalfiscal.inf.br/dce';

    public function build(DceCancelData $data): string
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('eventoDCe');
        $xml->writeAttribute('xmlns', self::NS);
        $xml->writeAttribute('versao', $data->versao);

        $xml->startElement('infEvento');
        $xml->writeAttribute('Id', $data->getEventId());

        $xml->writeElement('cOrgao', $data->cOrgao);
        $xml->writeElement('tpAmb', (string) $data->tpAmb);
        $xml->writeElement('tpEmit', (string) $data->tpEmit);
        $xml->writeElement('CNPJAutor', $data->cnpjAutor);

        if ($data->cnpjUsEmit !== null) {
            $xml->writeElement('CNPJUsEmit', $data->cnpjUsEmit);
        } elseif ($data->cpfUsEmit !== null) {
            $xml->writeElement('CPFUsEmit', $data->cpfUsEmit);
        } elseif ($data->idOutrosUsEmit !== null) {
            $xml->writeElement('IdOutrosUsEmit', $data->idOutrosUsEmit);
        }

        $xml->writeElement('chDCe', $data->chDCe);
        $xml->writeElement('dhEvento', $data->dhEvento);
        $xml->writeElement('tpEvento', $data->tpEvento);
        $xml->writeElement('nSeqEvento', $data->nSeqEvento);

        $xml->startElement('detEvento');
        $xml->writeAttribute('versaoEvento', $data->versaoEvento);

        $xml->startElement('evCancDCe');
        $xml->writeElement('descEvento', $data->descEvento);
        $xml->writeElement('nProt', $data->nProt);
        $xml->writeElement('xJust', $data->xJust);
        $xml->endElement(); // evCancDCe

        $xml->endElement(); // detEvento
        $xml->endElement(); // infEvento
        $xml->endElement(); // eventoDCe
        $xml->endDocument();

        return $xml->outputMemory();
    }
}

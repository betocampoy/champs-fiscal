<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Transmission;

use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentRequest;

final class DceAuthorizationPayloadBuilder
{
    public function build(DocumentRequest $request): object
    {
        $dom = new \DOMDocument();
        $dom->loadXML($request->getXml());

        $xmlFragment = $dom->saveXML($dom->documentElement);
        $payload = new \stdClass();
        $payload->any = new \SoapVar($xmlFragment, XSD_ANYXML);

        return $payload;
    }
}

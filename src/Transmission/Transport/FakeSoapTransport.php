<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

final class FakeSoapTransport implements SoapTransportInterface
{
    public function __construct() {}

    public function call(
        string $wsdl,
        string $operation,
        array $arguments = [],
        array $options = []
    ): SoapTransportResponse {
        $fakeResponseXml = <<<XML
<retDCE>
    <cStat>100</cStat>
    <xMotivo>Autorizado o uso do documento</xMotivo>
    <tpAmb>2</tpAmb>
    <nRec>123456789012345</nRec>
    <nProt>135240000000001</nProt>
    <chDCE>35260412345678000123570010000000011000000010</chDCE>
    <dhRecbto>2026-04-03T15:00:00-03:00</dhRecbto>
    <verAplic>SP_DCE_1.00</verAplic>
</retDCE>
XML;

        return new SoapTransportResponse(
            result: ['ok' => true],
            requestXml: '<soapRequest>fake</soapRequest>',
            responseXml: $fakeResponseXml,
            requestHeaders: 'fake-request-headers',
            responseHeaders: 'fake-response-headers',
        );
    }
}

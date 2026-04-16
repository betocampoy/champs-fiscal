<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

interface SoapTransportInterface
{
    public function call(
        string $wsdl,
        string $operation,
        array $arguments = [],
        array $options = []
    ): SoapTransportResponse;
}

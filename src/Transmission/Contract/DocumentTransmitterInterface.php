<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Contract;

use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentRequest;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use BetoCampoy\Champs\Fiscal\Transmission\Transport\SoapTlsCredentialsInterface;

interface DocumentTransmitterInterface
{
    public function transmit(
        DocumentRequest $request,
        SoapTlsCredentialsInterface $tlsCredentials,
    ): DocumentResponse;
}

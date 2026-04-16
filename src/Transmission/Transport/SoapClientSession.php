<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Transport;

final class SoapClientSession
{
    public function __construct(
        private readonly \SoapClient $client,
        private readonly SoapTlsTempFiles $tempFiles,
    ) {}

    public function getClient(): \SoapClient
    {
        return $this->client;
    }

    public function cleanup(): void
    {
        $this->tempFiles->cleanup();
    }

    public function __destruct()
    {
        $this->cleanup();
    }
}

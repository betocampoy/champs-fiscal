<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Contract;

interface DaceQrCodeUrlBuilderInterface
{
    public function buildNormal(
        string $baseUrl,
        string $accessKey,
        int $environment
    ): string;

    public function buildOfflineContingency(
        string $baseUrl,
        string $accessKey,
        int $environment,
        ?string $issuerDocument,
        string $signature
    ): string;
}

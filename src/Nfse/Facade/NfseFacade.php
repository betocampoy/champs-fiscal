<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Facade;

use BetoCampoy\Champs\Fiscal\Nfse\Authorization\NfseAuthorizationService;
use BetoCampoy\Champs\Fiscal\Nfse\Query\NfseQueryService;
use BetoCampoy\Champs\Fiscal\Nfse\Request\Builder\NfseAuthorizationPayload;
use BetoCampoy\Champs\Fiscal\Nfse\Request\Input\NfseEmitRequest;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use BetoCampoy\Champs\Certificate\ValueObject\OpenedCertificateData;

final class NfseFacade
{
    private NfseAuthorizationService $authService;
    private NfseQueryService $queryService;
    private int $environmentCode;

    public function __construct(
        string $environment = 'homolog',
        ?NfseAuthorizationService $authService = null,
        ?NfseQueryService $queryService = null,
    ) {
        $this->authService      = $authService  ?? new NfseAuthorizationService($environment);
        $this->queryService     = $queryService ?? new NfseQueryService($environment);
        $this->environmentCode  = $this->authService->getEnvironmentCode();
    }

    public function emit(
        NfseEmitRequest $request,
        OpenedCertificateData $certificate,
    ): DocumentResponse {
        $payload = new NfseAuthorizationPayload(
            request: $request,
            environment: $this->environmentCode,
        );

        return $this->authService->emit($payload, $certificate);
    }

    public function query(
        string $accessKey,
        OpenedCertificateData $certificate,
    ): DocumentResponse {
        return $this->queryService->query($accessKey, $certificate);
    }
}

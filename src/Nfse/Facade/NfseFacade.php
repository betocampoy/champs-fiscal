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

    /**
     * Retorna o XML da NFS-e decodificado (gzip+base64 → string XML).
     *
     * @throws \RuntimeException se a consulta falhar ou o XML não estiver disponível
     */
    public function getXml(
        string $accessKey,
        OpenedCertificateData $certificate,
    ): string {
        $response = $this->query($accessKey, $certificate);

        if (!$response->isSuccess()) {
            throw new \RuntimeException($response->getError() ?? 'Falha ao consultar NFS-e na SEFIN.');
        }

        $xmlGZipB64 = $response->getParsedValue('nfse_xml_b64');

        if (!$xmlGZipB64) {
            throw new \RuntimeException('XML da NFS-e não disponível na resposta da SEFIN.');
        }

        $xml = gzdecode(base64_decode((string)$xmlGZipB64));

        if ($xml === false) {
            throw new \RuntimeException('Falha ao decodificar o XML da NFS-e.');
        }

        return $xml;
    }

    /**
     * Retorna o conteúdo da DANFSE (HTML ou PDF).
     * Use $response->getRawResponse() para o body e
     * $response->getParsedValue('content_type') para o MIME type.
     */
    public function getDanfse(
        string $accessKey,
        OpenedCertificateData $certificate,
    ): DocumentResponse {
        return $this->queryService->danfse($accessKey, $certificate);
    }
}

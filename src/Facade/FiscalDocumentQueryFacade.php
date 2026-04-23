<?php

namespace BetoCampoy\Champs\Fiscal\Facade;

use BetoCampoy\Champs\Certificate\ValueObject\OpenedCertificateData;
use BetoCampoy\Champs\Fiscal\Dce\Query\DceQueryService;
use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Builder\DceQueryPayloadBuilder;
use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Input\DceQueryRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Normalizer\DceQueryRequestNormalizer;
use BetoCampoy\Champs\Fiscal\ValueObject\DfeAccessKey;
use BetoCampoy\Champs\Fiscal\ValueObject\DfeAccessKeyType;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use InvalidArgumentException;

class FiscalDocumentQueryFacade
{
    private int $environment;

    public function __construct(int $environment = 2)
    {
        $this->environment = $environment;
    }

    public function queryByAccessKey(
        string $accessKey,
        OpenedCertificateData $openedCertificate
    ): DocumentResponse {
        $key = DfeAccessKey::fromString($accessKey);

        if (!$key->isValid()) {
            throw new InvalidArgumentException('A chave de acesso informada é inválida.');
        }

        $type = DfeAccessKeyType::fromModelCode($key->getModelCode());

        return match ($type) {
            DfeAccessKeyType::DCE => $this->queryDce($key->getValue(), $openedCertificate),
            DfeAccessKeyType::NFE => $this->queryNfe($key->getValue(), $openedCertificate),
            default => throw new InvalidArgumentException(
                sprintf('Modelo [%s] não suportado para consulta.', $key->getModelCode())
            ),
        };
    }

    private function queryDce(
        string $accessKey,
        OpenedCertificateData $openedCertificate
    ): DocumentResponse {
        $request = new DceQueryRequest(
            accessKey: $accessKey,
            environment: $this->environment
        );

        $normalizer = new DceQueryRequestNormalizer();
        $request = $normalizer->normalize($request);

        $builder = new DceQueryPayloadBuilder();
        $payload = $builder->build($request);

        $service = new DceQueryService($this->environment);

        return $service->query(
            payload: $payload,
            certificate: $openedCertificate,
        );
    }

    private function queryNfe(
        string $accessKey,
        OpenedCertificateData $openedCertificate
    ): DocumentResponse {
        throw new InvalidArgumentException('Consulta de NF-e ainda não implementada.');
    }
}

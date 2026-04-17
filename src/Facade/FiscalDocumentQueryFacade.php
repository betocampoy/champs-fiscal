<?php

namespace BetoCampoy\Champs\Fiscal\Facade;


use BetoCampoy\Champs\Certificate\ValueObject\OpenedCertificateData;
use BetoCampoy\Champs\Fiscal\Dce\Query\DceQueryService;
use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Builder\DceQueryPayloadBuilder;
use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Input\DceQueryRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Normalizer\DceQueryRequestNormalizer;
use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;
use BetoCampoy\Champs\Fiscal\ValueObject\DfeAccessKey;
use BetoCampoy\Champs\Fiscal\ValueObject\DfeAccessKeyType;
use InvalidArgumentException;

final class FiscalDocumentQueryFacade
{
    public function __construct(
        private int $environment = 2
    ) {
    }

    /**
     * Consulta específica de DC-e
     */
    public function queryDce(
        string $accessKey,
        OpenedCertificateData $openedCertificate
    ): DocumentResponse {
        $accessKey = DfeAccessKey::fromString($accessKey)->getValue();

        $request = new DceQueryRequest(
            accessKey: $accessKey,
            environment: $this->environment
        );

        $normalizer = new DceQueryRequestNormalizer();
        $normalizedRequest = $normalizer->normalize($request);

        $builder = new DceQueryPayloadBuilder();
        $payload = $builder->build($normalizedRequest);

        $service = new DceQueryService($this->environment);

        return $service->query(
            payload: $payload,
            certificate: $openedCertificate,
        );
    }

    /**
     * Consulta específica de NF-e
     */
    public function queryNfe(
        string $accessKey,
        OpenedCertificateData $openedCertificate
    ): DocumentResponse {
//        $accessKey = DfeAccessKey::fromString($accessKey)->getValue();
//
//        $request = new NfeQueryRequest(
//            accessKey: $accessKey,
//            environment: $this->environment
//        );
//
//        $normalizer = new NfeQueryRequestNormalizer();
//        $normalizedRequest = $normalizer->normalize($request);
//
//        $builder = new NfeQueryPayloadBuilder();
//        $payload = $builder->build($normalizedRequest);
//
//        $service = new NfeQueryService($this->environment);
//
//        return $service->query(
//            payload: $payload,
//            certificate: $openedCertificate,
//        );
    }

    /**
     * Consulta genérica pela chave
     */
    public function queryByAccessKey(
        string $accessKey,
        OpenedCertificateData $openedCertificate
    ): DocumentResponse {
        $key = DfeAccessKey::fromString($accessKey);

        $documentType = $this->resolveDocumentType($key);

        if ($documentType === 'DCE') {
            return $this->queryDce($key->getValue(), $openedCertificate);
        }

        if ($documentType === 'NFE') {
            return $this->queryNfe($key->getValue(), $openedCertificate);
        }

        throw new InvalidArgumentException(
            'Não foi possível identificar o tipo do documento pela chave informada.'
        );
    }

    /**
     * Resolve o tipo do documento pela chave
     */
    private function resolveDocumentType(DfeAccessKey $key): string
    {
        /**
         * Aqui você pode evoluir depois para um resolver dedicado.
         * Por enquanto, usamos o modelo da chave.
         */

        if (!method_exists($key, 'getModel')) {
            throw new InvalidArgumentException(
                'Não foi possível identificar o modelo da chave.'
            );
        }

        $model = $key->getModel();

        return match ($model) {
            '60' => 'DCE', // ajustar se necessário
            '55' => 'NFE',
            default => throw new InvalidArgumentException(
                sprintf('Modelo [%s] não suportado para consulta.', $model)
            ),
        };
    }
}

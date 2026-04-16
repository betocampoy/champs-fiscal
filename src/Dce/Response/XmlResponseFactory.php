<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Response;

use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentOperation;
use InvalidArgumentException;

final class XmlResponseFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(DocumentOperation $operation, array $data): XmlResponse
    {
        return match ($operation) {
            DocumentOperation::SEND,
            DocumentOperation::RECEPCAO => new AuthorizationXmlResponse($data),

            DocumentOperation::QUERY => new QueryXmlResponse($data),

            DocumentOperation::CANCEL => new EventXmlResponse($data),

            default => throw new InvalidArgumentException(
                sprintf('Operação não suportada para XmlResponse: %s', $operation->value)
            ),
        };
    }
}

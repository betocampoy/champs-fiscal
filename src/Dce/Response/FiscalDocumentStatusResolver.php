<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Response;

use BetoCampoy\Champs\Fiscal\Transmission\Dto\DocumentResponse;

final class FiscalDocumentStatusResolver
{
    public const STATUS_READY = 'ready';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_AUTHORIZED = 'authorized';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ERROR = 'error';
    public const STATUS_CANCELING = 'canceling';
    public const STATUS_CANCELED = 'canceled';

    public function resolveAuthorizationStatus(DocumentResponse $response): string
    {
        if (!$response->isSuccess()) {
            return self::STATUS_ERROR;
        }

        $extractor = new DceAuthorizationResponseExtractor($response);

        if ($extractor->isAuthorized()) {
            return self::STATUS_AUTHORIZED;
        }

        if ($extractor->isRejected()) {
            return self::STATUS_REJECTED;
        }

        return self::STATUS_ERROR;
    }

    public function resolveCancelStatus(DocumentResponse $response): string
    {
        if (!$response->isSuccess()) {
            return self::STATUS_AUTHORIZED;
        }

        // 135 → Evento homologado (cancelado com sucesso)
        // 573 → Rejeição: Duplicidade de Evento
        return match ($response->getStatusCode()) {
            '135', // evento homologado
            '573'
            => self::STATUS_CANCELED,

            default => self::STATUS_AUTHORIZED,
        };
    }
}

<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Query\Builder;

use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Input\DceQueryRequest;

/**
 * Constrói o payload técnico da consulta da DC-e
 * a partir da request semântica.
 */
final class DceQueryPayloadBuilder
{
    public function build(DceQueryRequest $request): DceQueryPayload
    {
        $payload = [
            'accessKey' => $request->getAccessKey(),
            'environment' => $request->getEnvironment(),
            'version' => $request->getVersion(),
            'service' => $request->getService(),
        ];

        return new DceQueryPayload($this->filterNulls($payload));
    }

    private function filterNulls(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->filterNulls($value);
            }
        }

        return array_filter(
            $data,
            static fn ($value) => $value !== null && $value !== []
        );
    }
}

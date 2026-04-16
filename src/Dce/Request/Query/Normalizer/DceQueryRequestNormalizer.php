<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Query\Normalizer;

use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Input\DceQueryRequest;

final class DceQueryRequestNormalizer
{
    /**
     * @param array<string, string> $defaults
     */
    public function normalize(DceQueryRequest $request, array $defaults = []): DceQueryRequest
    {
        $defaults = array_replace($this->getInternalDefaults(), $defaults);

        $request->setAccessKey($this->digitsOnly($request->getAccessKey()));
        $request->setEnvironment($this->withDefault($request->getEnvironment(), $defaults['environment'] ?? null));
        $request->setVersion($this->withDefault($request->getVersion(), $defaults['version'] ?? null));
        $request->setService($this->withDefault($request->getService(), $defaults['service'] ?? null));

        return $request;
    }

    /**
     * @return array<string, string>
     */
    private function getInternalDefaults(): array
    {
        return [
            'version' => '1.00',
            'service' => 'CONSULTAR',
        ];
    }

    private function digitsOnly(?string $value): ?string
    {
        $value = $this->trimOrNull($value);

        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        return $digits === '' ? null : $digits;
    }

    private function trimOrNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function withDefault(?string $value, ?string $default): ?string
    {
        $value = $this->trimOrNull($value);

        if ($value !== null) {
            return $value;
        }

        return $this->trimOrNull($default);
    }
}

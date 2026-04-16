<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Query;

use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Builder\DceQueryPayload;
use InvalidArgumentException;

final class DceQueryDataFactory
{
    /**
     * @param DceQueryPayload $payload
     * @return DceQueryData
     */
    public function create(DceQueryPayload $payload): DceQueryData
    {
        $versao = $payload->getVersion();
        $tpAmb = $this->toInt($payload->getEnvironment() ?? null, 'tpAmb');
        $xServ = $payload->getService();
        $chDCe = $this->digits((string) ($payload->getAccessKey() ?? ''));

        if ($chDCe === '') {
            throw new InvalidArgumentException('Campo obrigatório não informado: chDCe');
        }

        return new DceQueryData(
            versao: $versao,
            tpAmb: $tpAmb,
            xServ: $xServ,
            chDCe: $chDCe,
        );
    }

    private function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function stringOrDefault(mixed $value, string $default): string
    {
        if ($value === null) {
            return $default;
        }

        $value = trim((string) $value);

        return $value === '' ? $default : $value;
    }

    private function toInt(mixed $value, string $field): int
    {
        if ($value === null || trim((string) $value) === '') {
            throw new InvalidArgumentException(sprintf(
                'Campo obrigatório não informado: %s',
                $field
            ));
        }

        if (!is_numeric((string) $value)) {
            throw new InvalidArgumentException(sprintf(
                'Campo inválido: %s',
                $field
            ));
        }

        return (int) $value;
    }
}

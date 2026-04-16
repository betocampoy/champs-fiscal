<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Event\Cancel;

use BetoCampoy\Champs\Fiscal\Dce\Request\Event\Builder\DceEventPayload;
use InvalidArgumentException;

final class DceCancelDataFactory
{
    public function create(DceEventPayload $payload): DceCancelData
    {
        $data = $payload->all();

        $cOrgao = $this->digits((string) ($data['cOrgao'] ?? ''));
        $tpAmb = $this->toInt($payload->getEnvironment(), 'tpAmb');
        $tpEmit = $this->toInt($data['tpEmit'] ?? null, 'tpEmit');
        $cnpjAutor = $this->digits((string) ($data['cnpjAutor'] ?? $payload->getAuthorDocument()));

        $cnpjUsEmit = $this->nullableDigits($data['cnpjUsEmit'] ?? null);
        $cpfUsEmit = $this->nullableDigits($data['cpfUsEmit'] ?? null);
        $idOutrosUsEmit = $this->nullableString($data['idOutrosUsEmit'] ?? null);

        $chDCe = $this->digits($payload->getAccessKey());
        $dhEvento = $this->requiredString($payload->getEventDate(), 'dhEvento');

        $tpEvento = $this->requiredString($payload->getEventType(), 'tpEvento');
        $nSeqEvento = $this->requiredString($payload->getSequence(), 'nSeqEvento');
        $versaoEvento = $this->requiredString($payload->getEventVersion(), 'versaoEvento');
        $descEvento = $this->requiredString($data['descEvento'] ?? null, 'descEvento');

        $nProt = $this->digits((string) ($data['nProt'] ?? ''));
        $xJust = $this->requiredString($payload->getJustification(), 'xJust');

        if ($cOrgao === '') {
            throw new InvalidArgumentException('Campo obrigatório não informado: cOrgao');
        }

        if ($cnpjAutor === '') {
            throw new InvalidArgumentException('Campo obrigatório não informado: cnpjAutor');
        }

        if ($chDCe === '') {
            throw new InvalidArgumentException('Campo obrigatório não informado: chDCe');
        }

        if ($nProt === '') {
            throw new InvalidArgumentException('Campo obrigatório não informado: nProt');
        }

        return new DceCancelData(
            versao: $payload->getVersion(),
            cOrgao: $cOrgao,
            tpAmb: $tpAmb,
            tpEmit: $tpEmit,
            cnpjAutor: $cnpjAutor,
            cnpjUsEmit: $cnpjUsEmit,
            cpfUsEmit: $cpfUsEmit,
            idOutrosUsEmit: $idOutrosUsEmit,
            chDCe: $chDCe,
            dhEvento: $dhEvento,
            tpEvento: $tpEvento,
            nSeqEvento: $nSeqEvento,
            versaoEvento: $versaoEvento,
            descEvento: $descEvento,
            nProt: $nProt,
            xJust: $xJust,
        );
    }

    private function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function nullableDigits(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = $this->digits((string) $value);

        return $digits === '' ? null : $digits;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function requiredString(mixed $value, string $field): string
    {
        if ($value === null) {
            throw new InvalidArgumentException(sprintf(
                'Campo obrigatório não informado: %s',
                $field
            ));
        }

        $value = trim((string) $value);

        if ($value === '') {
            throw new InvalidArgumentException(sprintf(
                'Campo obrigatório não informado: %s',
                $field
            ));
        }

        return $value;
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

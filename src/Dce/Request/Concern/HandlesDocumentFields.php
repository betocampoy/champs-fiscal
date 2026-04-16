<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Concern;

trait HandlesDocumentFields
{
    protected function splitDocument(?string $document): array
    {
        if ($document === null || trim($document) === '') {
            return [
                'cpf' => null,
                'cnpj' => null,
                'other' => null,
            ];
        }

        $document = trim($document);
        $numeric = preg_replace('/\D+/', '', $document) ?? '';

        if (strlen($numeric) === 11) {
            return [
                'cpf' => $numeric,
                'cnpj' => null,
                'other' => null,
            ];
        }

        if (strlen($numeric) === 14) {
            return [
                'cpf' => null,
                'cnpj' => $numeric,
                'other' => null,
            ];
        }

        return [
            'cpf' => null,
            'cnpj' => null,
            'other' => $document,
        ];
    }
}

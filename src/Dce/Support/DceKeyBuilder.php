<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Support;

use BetoCampoy\Champs\Fiscal\Brazil\UfCodeMap;
use RuntimeException;

class DceKeyBuilder
{
    public function generateRandomCode(?string $number): string
    {
        $numberDigits = $this->onlyDigits((string) $number);

        do {
            $randomCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (
            $this->isRepeatedSequence($randomCode)
            || ($numberDigits !== '' && $randomCode === str_pad(substr($numberDigits, -6), 6, '0', STR_PAD_LEFT))
        );

        return $randomCode;
    }
//    public function generateRandomCode(): string
//    {
//        return str_pad((string) random_int(0, 99999999), 6, '0', STR_PAD_LEFT);
//    }

    public function buildKeyWithoutDv(array $data): string
    {
        $state = strtoupper(trim((string)($data['state'] ?? '')));
        $stateCode = UfCodeMap::codeFromState($state);

        if (!$stateCode) {
            throw new RuntimeException("UF inválida para gerar chave da DC-e: {$state}");
        }

        $issuedAt = (string)($data['issued_at'] ?? '');
        $emitDocument = $this->onlyDigits((string)($data['emit_document'] ?? ''));
        $series = (string)($data['series'] ?? '');
        $number = (string)($data['number'] ?? '');
        $emissionType = (string)($data['emission_type'] ?? '');
        $randomCode = $this->onlyDigits((string)($data['random_code'] ?? ''));

        if ($issuedAt === '') {
            throw new RuntimeException('issued_at é obrigatório para gerar a chave da DC-e.');
        }

        if ($emitDocument === '') {
            throw new RuntimeException('emit_document é obrigatório para gerar a chave da DC-e.');
        }

        if ($series === '') {
            throw new RuntimeException('series é obrigatório para gerar a chave da DC-e.');
        }

        if ($number === '') {
            throw new RuntimeException('number é obrigatório para gerar a chave da DC-e.');
        }

        if ($emissionType === '') {
            throw new RuntimeException('emission_type é obrigatório para gerar a chave da DC-e.');
        }

        if ($randomCode === '') {
            throw new RuntimeException('random_code é obrigatório para gerar a chave da DC-e.');
        }

        $aamm = date('ym', strtotime($issuedAt));
        $model = '57';

        return
            $stateCode .
            $aamm .
            str_pad($emitDocument, 14, '0', STR_PAD_LEFT) .
            $model .
            str_pad($series, 3, '0', STR_PAD_LEFT) .
            str_pad($number, 9, '0', STR_PAD_LEFT) .
            $emissionType .
            str_pad($randomCode, 8, '0', STR_PAD_LEFT);
    }

    public function calculateDv(string $keyWithoutDv): int
    {
        $weights = [2, 3, 4, 5, 6, 7, 8, 9];
        $sum = 0;
        $weightIndex = 0;

        for ($pos = strlen($keyWithoutDv) - 1; $pos >= 0; $pos--) {
            $sum += ((int) $keyWithoutDv[$pos]) * $weights[$weightIndex];
            $weightIndex = ($weightIndex + 1) % count($weights);
        }

        $mod = $sum % 11;

        return $mod < 2 ? 0 : 11 - $mod;
    }

    public function buildAccessKey(array $data): string
    {
        $keyWithoutDv = $this->buildKeyWithoutDv($data);
        $dv = $this->calculateDv($keyWithoutDv);

        return $keyWithoutDv . $dv;
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
    private function isRepeatedSequence(string $value): bool
    {
        return preg_match('/^(\d)\1{5}$/', $value) === 1;
    }
}

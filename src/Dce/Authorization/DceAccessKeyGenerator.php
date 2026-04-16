<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Authorization;

use DateTimeImmutable;
use InvalidArgumentException;

final class DceAccessKeyGenerator
{
    public function generate(
        string $cUF,
        DateTimeImmutable $dhEmi,
        string $cnpjAutor,
        string $mod,
        string $serie,
        string $nDC,
        int $tpEmis,
        int $tpEmit,
        int $nSiteAutoriz,
        string $cDC,
    ): string {
        if ($tpEmis < 0 || $tpEmis > 9) {
            throw new InvalidArgumentException('tpEmis inválido para composição da chave.');
        }

        if ($tpEmit < 0 || $tpEmit > 9) {
            throw new InvalidArgumentException('tpEmit inválido para composição da chave.');
        }

        if ($nSiteAutoriz < 0 || $nSiteAutoriz > 9) {
            throw new InvalidArgumentException('nSiteAutoriz inválido para composição da chave.');
        }

        $base = sprintf(
            '%s%s%s%s%s%s%s%s%s%s',
            str_pad($this->onlyDigits($cUF), 2, '0', STR_PAD_LEFT),
            $dhEmi->format('ym'),
            str_pad($this->onlyDigits($cnpjAutor), 14, '0', STR_PAD_LEFT),
            str_pad($this->onlyDigits($mod), 2, '0', STR_PAD_LEFT),
            str_pad($this->onlyDigits($serie), 3, '0', STR_PAD_LEFT),
            str_pad($this->onlyDigits($nDC), 9, '0', STR_PAD_LEFT),
            (string) $tpEmis,
            (string) $tpEmit,
            (string) $nSiteAutoriz,
            str_pad($this->onlyDigits($cDC), 6, '0', STR_PAD_LEFT),
        );

        if (strlen($base) !== 43) {
            throw new InvalidArgumentException(
                sprintf('Base da chave deve ter 43 dígitos. Gerado: %d [%s]', strlen($base), $base)
            );
        }

        return $base . $this->calculateDv($base);
    }

    public function calculateDv(string $base43): int
    {
        $base43 = $this->onlyDigits($base43);

        if (strlen($base43) !== 43) {
            throw new InvalidArgumentException('A base para cálculo do DV deve ter 43 dígitos.');
        }

        $sum = 0;
        $weight = 2;

        for ($i = strlen($base43) - 1; $i >= 0; $i--) {
            $sum += ((int) $base43[$i]) * $weight;
            $weight = $weight === 9 ? 2 : $weight + 1;
        }

        $rest = $sum % 11;

        return ($rest === 0 || $rest === 1) ? 0 : 11 - $rest;
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}

<?php

namespace BetoCampoy\Champs\Fiscal\ValueObject;

enum DfeAccessKeyType: string
{
    case NFE = '55';
    case CTE = '57';
    case MDFE = '58';
    case CTE_OS = '67';
    case DCE = '99';
    case UNKNOWN = '00';

    public static function fromModelCode(string $modelCode): self
    {
        return match ($modelCode) {
            '55' => self::NFE,
            '57' => self::CTE,
            '58' => self::MDFE,
            '67' => self::CTE_OS,
            '99' => self::DCE,
            default => self::UNKNOWN,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NFE => 'NF-e',
            self::CTE => 'CT-e',
            self::MDFE => 'MDF-e',
            self::CTE_OS => 'CT-e OS',
            self::DCE => 'DC-e',
            self::UNKNOWN => 'Desconhecido',
        };
    }
}

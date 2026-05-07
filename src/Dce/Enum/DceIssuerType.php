<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Enum;

enum DceIssuerType: int
{
    case FISCO = 0;
    case MARKETPLACE = 1;
    case OWN = 2;
    case CARRIER = 3;

    public function label(): string
    {
        return match ($this) {
            self::FISCO => 'Fisco',
            self::MARKETPLACE => 'Marketplace',
            self::OWN => 'Empresa Emissão Própria',
            self::CARRIER => 'Transportadora',
        };
    }

    public function requiresGroup(): string
    {
        return match ($this) {
            self::FISCO => 'fisco',
            self::MARKETPLACE => 'marketplace',
            self::OWN => 'emissaoPropria',
            self::CARRIER => 'transportadora',
        };
    }
}

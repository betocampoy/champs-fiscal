<?php

namespace BetoCampoy\Champs\Fiscal\Transmission\Dto;

enum DocumentOperation: string
{
    case SEND = 'send';
    case RECEPCAO = 'recepcao';
    case CANCEL = 'cancel';
    case QUERY = 'query';

    public function isSend(): bool
    {
            return $this === self::SEND || $this === self::RECEPCAO;
    }
}

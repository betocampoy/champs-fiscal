<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Mapper;

interface RequestMapperInterface
{
    public function supports(object $source): bool;
}

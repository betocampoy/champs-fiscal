<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Query\Mapper;

use BetoCampoy\Champs\Fiscal\Dce\Request\Mapper\RequestMapperInterface;
use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Input\DceQueryRequest;

interface DceQueryRequestMapperInterface extends RequestMapperInterface
{
    public function map(object $source): DceQueryRequest;
}

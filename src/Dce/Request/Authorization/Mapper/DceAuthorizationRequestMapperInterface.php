<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Mapper;

use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAuthorizationRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Mapper\RequestMapperInterface;

interface DceAuthorizationRequestMapperInterface extends RequestMapperInterface
{
    public function map(object $source): DceAuthorizationRequest;
}

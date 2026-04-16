<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Event\Mapper;

use BetoCampoy\Champs\Fiscal\Dce\Request\Event\Input\DceEventRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Mapper\RequestMapperInterface;

interface DceEventRequestMapperInterface extends RequestMapperInterface
{
    public function map(object $source): DceEventRequest;
}


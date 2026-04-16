<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Contract;

use BetoCampoy\Champs\Fiscal\Dce\Dace\Dto\DaceData;

interface DaceDataMapperInterface
{
    public function map(DcePrintableDocumentInterface $document): DaceData;
}

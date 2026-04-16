<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Contract;

interface DcePrintableDocumentInterface
{
    public function getDceAuthorizedXml(): ?string;

}

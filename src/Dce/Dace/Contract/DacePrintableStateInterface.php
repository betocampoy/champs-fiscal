<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Contract;

interface DacePrintableStateInterface
{
    public function getDacePrintedAt(): ?\DateTimeInterface;

    public function isDacePrinted(): bool;

    public function markDaceAsPrinted(\DateTimeInterface $printedAt): void;

}

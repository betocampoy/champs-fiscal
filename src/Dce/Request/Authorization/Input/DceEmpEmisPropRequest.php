<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

final class DceEmpEmisPropRequest
{
    private ?string $cnpj = null;
    private ?string $name = null;

    public function getCnpj(): ?string
    {
        return $this->cnpj;
    }

    public function setCnpj(?string $cnpj): self
    {
        $this->cnpj = $cnpj;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }
}

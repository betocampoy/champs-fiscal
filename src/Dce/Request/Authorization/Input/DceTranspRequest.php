<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

/**
 * Grupo Transp da DC-e.
 *
 * Usado quando tpEmit = 3 (Transportadora).
 */
final class DceTranspRequest
{
    /**
     * CNPJ da transportadora.
     * Choice com CPF.
     */
    private ?string $cnpj = null;

    /**
     * CPF da transportadora.
     * Choice com CNPJ.
     */
    private ?string $cpf = null;

    /**
     * xNome
     * Nome ou razão social da transportadora.
     */
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

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(?string $cpf): self
    {
        $this->cpf = $cpf;
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

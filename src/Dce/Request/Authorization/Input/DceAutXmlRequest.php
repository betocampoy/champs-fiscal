<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

/**
 * Grupo autXML da DC-e.
 *
 * Representa uma pessoa autorizada a acessar o XML da DC-e.
 * O XSD permite até 10 ocorrências.
 */
final class DceAutXmlRequest
{
    /**
     * CNPJ do autorizado.
     * Choice com CPF.
     */
    private ?string $cnpj = null;

    /**
     * CPF do autorizado.
     * Choice com CNPJ.
     */
    private ?string $cpf = null;

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
}

<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

use BetoCampoy\Champs\Fiscal\Dce\Request\Concern\HandlesDocumentFields;

class DceEmitRequest
{
    use HandlesDocumentFields;

    private ?string $cpf = null;
    private ?string $cnpj = null;
    private ?string $other = null;
    private ?string $name = null;
    private ?DceEmitAddressRequest $address = null;

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(?string $cpf): self
    {
        $this->cpf = $cpf;
        return $this;
    }

    public function getCnpj(): ?string
    {
        return $this->cnpj;
    }

    public function setCnpj(?string $cnpj): self
    {
        $this->cnpj = $cnpj;
        return $this;
    }

    public function getOther(): ?string
    {
        return $this->other;
    }

    public function setOther(?string $other): self
    {
        $this->other = $other;
        return $this;
    }

    public function setDocument(?string $document): self
    {
        $parts = $this->splitDocument($document);

        $this->cpf = $parts['cpf'];
        $this->cnpj = $parts['cnpj'];
        $this->other = $parts['other'];

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

    public function getAddress(): ?DceEmitAddressRequest
    {
        return $this->address;
    }

    public function setAddress(?DceEmitAddressRequest $address): self
    {
        $this->address = $address;
        return $this;
    }
}

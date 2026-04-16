<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input;

use BetoCampoy\Champs\Fiscal\Dce\Request\Concern\HandlesDocumentFields;

/**
 * Grupo dest da DC-e.
 *
 * XSD:
 * infDCe/dest
 */
final class DceDestRequest
{
    use HandlesDocumentFields;

    /**
     * CNPJ do destinatário.
     * Choice com CPF e otherId.
     */
    private ?string $cnpj = null;

    /**
     * CPF do destinatário.
     * Choice com CNPJ e otherId.
     */
    private ?string $cpf = null;

    /**
     * idOutros
     * Identificação alternativa do destinatário.
     * Choice com CNPJ e CPF.
     */
    private ?string $otherId = null;

    /**
     * xNome
     * Razão social ou nome do destinatário.
     */
    private ?string $name = null;

    /**
     * enderDest
     * Endereço do destinatário.
     */
    private ?DceDestAddressRequest $address = null;

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

    public function getOtherId(): ?string
    {
        return $this->otherId;
    }

    public function setOtherId(?string $otherId): self
    {
        $this->otherId = $otherId;
        return $this;
    }

    public function setDocument(?string $document): self
    {
        $parts = $this->splitDocument($document);

        $this->cpf = $parts['cpf'];
        $this->cnpj = $parts['cnpj'];
        $this->otherId = $parts['other'];

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

    public function getAddress(): ?DceDestAddressRequest
    {
        return $this->address;
    }

    public function setAddress(?DceDestAddressRequest $address): self
    {
        $this->address = $address;
        return $this;
    }
}

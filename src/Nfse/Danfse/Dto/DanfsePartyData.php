<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Danfse\Dto;

/**
 * Prestador, tomador ou intermediário, com os valores já formatados para exibição.
 */
final class DanfsePartyData
{
    public function __construct(
        public readonly ?string $document,          // CNPJ/CPF/NIF já formatado
        public readonly ?string $name,
        public readonly ?string $municipalRegistration,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $address,           // logradouro, número, complemento, bairro
        public readonly ?string $city,              // "Ibitinga - SP" (ou o código IBGE quando o nome não está no XML)
        public readonly ?string $zipCode,
        public readonly ?string $simplesNacional = null,        // só prestador
        public readonly ?string $simplesNacionalRegime = null,  // só prestador
    ) {
    }
}

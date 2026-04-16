<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Validator;

/**
 * Representa um erro de validação.
 */
final class ValidationError
{
    public function __construct(
        private readonly string $field,
        private readonly string $message,
    ) {}

    /**
     * Campo onde ocorreu o erro.
     *
     * Exemplo:
     * - ide.number
     * - emit.cnpj
     * - det[0].prod.name
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Mensagem descritiva do erro.
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}

<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Validator;

/**
 * Resultado de uma validação.
 */
final class ValidationResult
{
    /**
     * @var ValidationError[]
     */
    private array $errors = [];

    /**
     * Adiciona um erro de validação.
     */
    public function addError(string $field, string $message): void
    {
        $this->errors[] = new ValidationError($field, $message);
    }

    /**
     * Adiciona múltiplos erros.
     *
     * @param ValidationError[] $errors
     */
    public function addErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $this->errors[] = $error;
        }
    }

    /**
     * Mescla outro resultado de validação.
     */
    public function merge(self $other): void
    {
        $this->addErrors($other->getErrors());
    }

    /**
     * Retorna todos os erros.
     *
     * @return ValidationError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Indica se a validação passou sem erros.
     */
    public function isValid(): bool
    {
        return $this->errors === [];
    }

    /**
     * Retorna os erros em formato simples (array).
     *
     * Útil para JSON/API/UI.
     */
    public function toArray(): array
    {
        return array_map(
            fn (ValidationError $error) => [
                'field' => $error->getField(),
                'message' => $error->getMessage(),
            ],
            $this->errors
        );
    }
}

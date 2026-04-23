<?php

namespace BetoCampoy\Champs\Fiscal\ValueObject;

use InvalidArgumentException;

final class DfeAccessKey
{
    private string $value;

    private string $ufCode;
    private string $yearMonth;
    private string $cnpj;
    private string $modelCode;
    private string $serie;
    private string $number;
    private string $emissionTypeCode;
    private string $numericCode;
    private string $checkDigit;

    private DfeAccessKeyType $type;

    private bool $valid;

    private function __construct(string $value)
    {
        $normalized = preg_replace('/\D+/', '', $value ?? '');

        if (!is_string($normalized) || strlen($normalized) !== 44) {
            throw new InvalidArgumentException('A chave de acesso deve conter exatamente 44 dígitos.');
        }

        $this->value = $normalized;

        $this->parse();
        $this->type = DfeAccessKeyType::fromModelCode($this->modelCode);

        // 🔥 VALID não depende do tipo!
        $this->valid = $this->validateCheckDigit();
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function parse(): void
    {
        $this->ufCode = substr($this->value, 0, 2);
        $this->yearMonth = substr($this->value, 2, 4);
        $this->cnpj = substr($this->value, 6, 14);
        $this->modelCode = substr($this->value, 20, 2);
        $this->serie = substr($this->value, 22, 3);
        $this->number = substr($this->value, 25, 9);
        $this->emissionTypeCode = substr($this->value, 34, 1);
        $this->numericCode = substr($this->value, 35, 8);
        $this->checkDigit = substr($this->value, 43, 1);
    }

    private function validateCheckDigit(): bool
    {
        $key43 = substr($this->value, 0, 43);

        $weights = [2,3,4,5,6,7,8,9];
        $weightIndex = 0;
        $sum = 0;

        for ($i = 42; $i >= 0; $i--) {
            $sum += intval($key43[$i]) * $weights[$weightIndex];
            $weightIndex = ($weightIndex + 1) % 8;
        }

        $mod = $sum % 11;
        $dv = ($mod === 0 || $mod === 1) ? 0 : 11 - $mod;

        return (string)$dv === $this->checkDigit;
    }

    // =========================
    // GETTERS
    // =========================

    public function getValue(): string
    {
        return $this->value;
    }

    public function getUfCode(): string
    {
        return $this->ufCode;
    }

    public function getYearMonth(): string
    {
        return $this->yearMonth;
    }

    public function getYear(): string
    {
        return '20' . substr($this->yearMonth, 0, 2);
    }

    public function getMonth(): string
    {
        return substr($this->yearMonth, 2, 2);
    }

    public function getCnpj(): string
    {
        return $this->cnpj;
    }

    public function getModelCode(): string
    {
        return $this->modelCode;
    }

    public function getSerie(): string
    {
        return $this->serie;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getEmissionTypeCode(): string
    {
        return $this->emissionTypeCode;
    }

    public function getNumericCode(): string
    {
        return $this->numericCode;
    }

    public function getCheckDigit(): string
    {
        return $this->checkDigit;
    }

    public function getType(): DfeAccessKeyType
    {
        return $this->type;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    // =========================
    // DEBUG / OUTPUT
    // =========================

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'ufCode' => $this->ufCode,
            'yearMonth' => $this->yearMonth,
            'year' => $this->getYear(),
            'month' => $this->getMonth(),
            'cnpj' => $this->cnpj,
            'modelCode' => $this->modelCode,
            'type' => $this->type->name,
            'typeLabel' => $this->type->label(),
            'serie' => $this->serie,
            'number' => $this->number,
            'emissionTypeCode' => $this->emissionTypeCode,
            'numericCode' => $this->numericCode,
            'checkDigit' => $this->checkDigit,
            'valid' => $this->valid,
        ];
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

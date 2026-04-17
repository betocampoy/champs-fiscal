<?php

namespace BetoCampoy\Champs\Fiscal\ValueObject;

use BetoCampoy\Champs\Fiscal\Brazil\UfCodeMap;
use InvalidArgumentException;

final class DfeAccessKey
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = preg_replace('/\D+/', '', $value ?? '');

        if (!is_string($normalized) || strlen($normalized) !== 44) {
            throw new InvalidArgumentException('A chave de acesso deve conter exatamente 44 dígitos.');
        }

        $this->value = $normalized;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getUfCode(): string
    {
        return substr($this->value, 0, 2);
    }

    public function getUf(): ?string
    {
        return UfCodeMap::stateFromCode($this->getUfCode());
    }

    /**
     * AAMM
     */
    public function getYearMonth(): string
    {
        return substr($this->value, 2, 4);
    }

    public function getYear(): string
    {
        return '20' . substr($this->value, 2, 2);
    }

    public function getMonth(): string
    {
        return substr($this->value, 4, 2);
    }

    public function getCnpj(): string
    {
        return substr($this->value, 6, 14);
    }

    public function getModelCode(): string
    {
        return substr($this->value, 20, 2);
    }

    public function getType(): DfeAccessKeyType
    {
        return DfeAccessKeyType::fromModelCode($this->getModelCode());
    }

    public function isNfe(): bool
    {
        return $this->getType() === DfeAccessKeyType::NFE;
    }

    public function isCte(): bool
    {
        return $this->getType() === DfeAccessKeyType::CTE;
    }

    public function isCteOs(): bool
    {
        return $this->getType() === DfeAccessKeyType::CTE_OS;
    }

    public function isMdfe(): bool
    {
        return $this->getType() === DfeAccessKeyType::MDFE;
    }

    public function isDce(): bool
    {
        return $this->getType() === DfeAccessKeyType::DCE;
    }

    public function getSerie(): string
    {
        return substr($this->value, 22, 3);
    }

    public function getNumber(): string
    {
        return substr($this->value, 25, 9);
    }

    public function getEmissionTypeCode(): string
    {
        return substr($this->value, 34, 1);
    }

    public function getNumericCode(): string
    {
        return substr($this->value, 35, 8);
    }

    public function getCheckDigit(): string
    {
        return substr($this->value, 43, 1);
    }

    public function isFromUfCode(string $ufCode): bool
    {
        return $this->getUfCode() === $ufCode;
    }

    public function isFromUf(string $uf): bool
    {
        $currentUf = $this->getUf();

        return $currentUf !== null && strtoupper($currentUf) === strtoupper($uf);
    }

    public function belongsToCnpj(string $cnpj): bool
    {
        $normalized = preg_replace('/\D+/', '', $cnpj ?? '');

        return $this->getCnpj() === $normalized;
    }

    public function isValid(): bool
    {
        return $this->calculateCheckDigit() === (int) $this->getCheckDigit();
    }

    public function assertValid(): void
    {
        if (!$this->isValid()) {
            throw new InvalidArgumentException('A chave de acesso informada possui dígito verificador inválido.');
        }
    }

    public function toArray(): array
    {
        return [
            'value' => $this->getValue(),
            'ufCode' => $this->getUfCode(),
            'uf' => $this->getUf(),
            'yearMonth' => $this->getYearMonth(),
            'year' => $this->getYear(),
            'month' => $this->getMonth(),
            'cnpj' => $this->getCnpj(),
            'modelCode' => $this->getModelCode(),
            'type' => $this->getType()->name,
            'typeLabel' => $this->getType()->label(),
            'serie' => $this->getSerie(),
            'number' => $this->getNumber(),
            'emissionTypeCode' => $this->getEmissionTypeCode(),
            'numericCode' => $this->getNumericCode(),
            'checkDigit' => $this->getCheckDigit(),
            'valid' => $this->isValid(),
        ];
    }

    private function calculateCheckDigit(): int
    {
        $base = substr($this->value, 0, 43);
        $weights = [4, 3, 2, 9, 8, 7, 6, 5];

        $sum = 0;
        $weightIndex = 0;

        for ($i = strlen($base) - 1; $i >= 0; $i--) {
            $digit = (int) $base[$i];
            $sum += $digit * $weights[$weightIndex];
            $weightIndex = ($weightIndex + 1) % count($weights);
        }

        $mod = $sum % 11;
        $dv = 11 - $mod;

        return $dv >= 10 ? 0 : $dv;
    }
}

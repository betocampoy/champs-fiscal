<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Import\Dto;

final class DocumentImportResult
{
    /**
     * @param array<int, string> $errors
     */
    public function __construct(
        private readonly bool $success,
        private readonly string $status,
        private readonly ?string $documentType = null,
        private readonly ?string $documentKey = null,
        private readonly ?int $documentId = null,
        private readonly ?string $message = null,
        private readonly array $errors = [],
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDocumentType(): ?string
    {
        return $this->documentType;
    }

    public function getDocumentKey(): ?string
    {
        return $this->documentKey;
    }

    public function getDocumentId(): ?int
    {
        return $this->documentId;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function imported(
        string $documentType,
        ?string $documentKey,
        int $documentId,
        ?string $message = null
    ): self {
        return new self(true, 'imported', $documentType, $documentKey, $documentId, $message);
    }

    public static function duplicate(
        string $documentType,
        ?string $documentKey,
        ?int $documentId = null,
        ?string $message = null
    ): self {
        return new self(true, 'duplicate', $documentType, $documentKey, $documentId, $message);
    }

    /**
     * @param array<int, string> $errors
     */
    public static function invalid(?string $message = null, array $errors = []): self
    {
        return new self(false, 'invalid', null, null, null, $message, $errors);
    }

    /**
     * @param array<int, string> $errors
     */
    public static function failed(?string $message = null, array $errors = []): self
    {
        return new self(false, 'failed', null, null, null, $message, $errors);
    }
}

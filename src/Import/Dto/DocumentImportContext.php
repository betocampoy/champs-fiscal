<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Import\Dto;

final class DocumentImportContext
{
    public function __construct(
        private readonly int $batchId,
        private readonly int $itemId,
        private readonly int $empresaId,
        private readonly int $clienteId,
        private readonly int $unidadeId,
        private readonly ?int $createdBy = null,
    ) {
    }

    public function getBatchId(): int
    {
        return $this->batchId;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getEmpresaId(): int
    {
        return $this->empresaId;
    }

    public function getClienteId(): int
    {
        return $this->clienteId;
    }

    public function getUnidadeId(): int
    {
        return $this->unidadeId;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }
}

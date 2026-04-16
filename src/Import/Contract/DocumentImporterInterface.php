<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Import\Contract;

use BetoCampoy\Champs\Fiscal\Import\Dto\DocumentImportContext;
use BetoCampoy\Champs\Fiscal\Import\Dto\DocumentImportResult;

interface DocumentImporterInterface
{
    public function supports(string $documentType): bool;

    public function import(string $xmlContent, DocumentImportContext $context): DocumentImportResult;
}

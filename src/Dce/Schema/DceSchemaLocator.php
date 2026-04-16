<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Schema;

final class DceSchemaLocator
{
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        // Aponta para: src/Champs/Fiscal
        $this->basePath = $basePath ?? dirname(__DIR__, 2);
    }

    public function getEmissionXsd(string $version = '1.00'): string
    {
        return $this->resolve(
            sprintf('resources/schemas/dce/%s/authorization/dce_v%s.xsd', $version, $version)
        );
    }

    public function getQueryXsd(string $version = '1.00'): string
    {
        return $this->resolve(
            sprintf('resources/schemas/dce/%s/query/consSitDCe_v%s.xsd', $version, $version)
        );
    }

    public function getEventXsd(string $version = '1.00'): string
    {
        return $this->resolve(
            sprintf('resources/schemas/dce/%s/event/eventoDCe_v%s.xsd', $version, $version)
        );
    }

    private function resolve(string $relativePath): string
    {
        $fullPath = rtrim($this->basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relativePath;

        if (!file_exists($fullPath)) {
            throw new \RuntimeException(sprintf('XSD não encontrado: %s', $fullPath));
        }

        return $fullPath;
    }
}

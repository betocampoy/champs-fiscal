<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Query;

use DOMDocument;
use InvalidArgumentException;
use LibXMLError;
use RuntimeException;

final class DceQueryXsdValidator
{
    public function validate(string $xml, string $xsdPath): void
    {
        if (trim($xml) === '') {
            throw new InvalidArgumentException('XML de consulta não informado para validação.');
        }

        if (trim($xsdPath) === '') {
            throw new InvalidArgumentException('Caminho do XSD da consulta não informado.');
        }

        if (!is_file($xsdPath)) {
            throw new InvalidArgumentException("Arquivo XSD não encontrado: {$xsdPath}");
        }

        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $dom = new DOMDocument('1.0', 'UTF-8');

            if (!$dom->loadXML($xml, LIBXML_NOBLANKS)) {
                throw new RuntimeException($this->buildLibxmlMessage('Falha ao carregar XML da consulta.'));
            }

            if (!$dom->schemaValidate($xsdPath)) {
                throw new RuntimeException($this->buildLibxmlMessage('Falha na validação XSD da consulta.'));
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function buildLibxmlMessage(string $prefix): string
    {
        $errors = libxml_get_errors();

        if ($errors === []) {
            return $prefix;
        }

        $messages = array_map(
            fn (LibXMLError $error) => $this->formatLibxmlError($error),
            $errors
        );

        return $prefix . ' ' . implode(' | ', $messages);
    }

    private function formatLibxmlError(LibXMLError $error): string
    {
        $message = trim($error->message);

        return sprintf(
            '[line %d, column %d] %s',
            $error->line,
            $error->column,
            $message
        );
    }
}

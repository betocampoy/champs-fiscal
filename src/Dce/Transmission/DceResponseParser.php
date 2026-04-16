<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Transmission;

final class DceResponseParser
{
    /**
     * @return array<string, mixed>
     */
    public function parse(string $xml): array
    {
        $xml = trim($xml);

        if ($xml === '') {
            throw new \InvalidArgumentException('O XML de resposta da DC-e é obrigatório.');
        }

        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $root = simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NOCDATA);

            if ($root === false) {
                throw new \InvalidArgumentException('Não foi possível interpretar o XML de resposta da DC-e.');
            }

            $body = $this->findSoapBody($root);

            if ($body === null) {
                throw new \RuntimeException('Não foi possível localizar o Body do envelope SOAP.');
            }

            $resultNode = $this->getFirstElementChild($body);

            if ($resultNode === null) {
                throw new \RuntimeException('Não foi possível localizar o nó de resultado da resposta SOAP.');
            }

            $payloadNode = $this->getFirstElementChild($resultNode);

            if ($payloadNode === null) {
                throw new \RuntimeException('Não foi possível localizar o payload XML da resposta.');
            }

            $payloadArray = $this->simpleXmlToArray($payloadNode);

            return [
                'response_type' => $this->detectResponseType($payloadNode->getName()),
                'payload_root' => $payloadNode->getName(),
                'payload' => $payloadArray,
                'raw_xml' => $xml,
            ];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function detectResponseType(string $payloadRootName): string
    {
        return match ($payloadRootName) {
            'retDCe' => 'authorization',
            'retConsSitDCe' => 'query',
            'retEventoDCe' => 'event',
            default => 'unknown',
        };
    }

    private function findSoapBody(\SimpleXMLElement $root): ?\SimpleXMLElement
    {
        $namespaces = $root->getNamespaces(true);

        foreach (['env', 'soap'] as $prefix) {
            if (!isset($namespaces[$prefix])) {
                continue;
            }

            $children = $root->children($namespaces[$prefix]);

            if (isset($children->Body)) {
                return $children->Body;
            }
        }

        if (isset($root->Body)) {
            return $root->Body;
        }

        return null;
    }

    private function getFirstElementChild(\SimpleXMLElement $element): ?\SimpleXMLElement
    {
        foreach ($element->children() as $child) {
            return $child;
        }

        $namespaces = $element->getNamespaces(true);

        foreach ($namespaces as $namespace) {
            foreach ($element->children($namespace) as $child) {
                return $child;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function simpleXmlToArray(\SimpleXMLElement $element): array
    {
        $json = json_encode($element);

        if ($json === false) {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }
}

<?php

namespace BetoCampoy\Champs\Fiscal\Xml;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;

final class XmlSigner
{
    private const XMLDSIG_NS = 'http://www.w3.org/2000/09/xmldsig#';

    public function sign(
        string $xml,
        string $referenceId,
        string $privateKeyPem,
        string $certificatePem,
        XmlSignatureConfig $config
    ): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        if (!$dom->loadXML($xml, LIBXML_NOBLANKS)) {
            throw new RuntimeException('Falha ao carregar XML.');
        }

        $referenceNode = $this->findNodeById($dom, $referenceId);

        // Monta a estrutura da assinatura com DigestValue vazio.
        $signature = $this->buildSignature(
            dom: $dom,
            referenceId: $referenceId,
            digestValue: '',
            certificatePem: $certificatePem,
            config: $config
        );

        // Anexa a assinatura no local final antes de calcular digest e SignedInfo.
        $this->appendSignature($dom, $signature, $config);

        $digestValue = $this->calculateDigest($referenceNode, $config);

        $digestNode = $signature->getElementsByTagNameNS(self::XMLDSIG_NS, 'DigestValue')->item(0);

        if (!$digestNode instanceof DOMElement) {
            throw new RuntimeException('Elemento DigestValue não encontrado na assinatura.');
        }

        $digestNode->nodeValue = $digestValue;

        $signedInfo = $signature->getElementsByTagNameNS(self::XMLDSIG_NS, 'SignedInfo')->item(0);

        if (!$signedInfo instanceof DOMElement) {
            throw new RuntimeException('Elemento SignedInfo não encontrado na assinatura.');
        }

        $canonicalSignedInfo = $signedInfo->C14N(false, false);

        if ($canonicalSignedInfo === false) {
            throw new RuntimeException('Falha ao canonicalizar SignedInfo.');
        }

        $signatureValue = $this->signData(
            data: $canonicalSignedInfo,
            privateKeyPem: $privateKeyPem,
            algorithm: $config->opensslAlgorithm
        );

        $signatureNode = $signature->getElementsByTagNameNS(self::XMLDSIG_NS, 'SignatureValue')->item(0);

        if (!$signatureNode instanceof DOMElement) {
            throw new RuntimeException('Elemento SignatureValue não encontrado na assinatura.');
        }

        $signatureNode->nodeValue = $signatureValue;

        return $dom->saveXML();
    }

    private function findNodeById(DOMDocument $dom, string $id): DOMElement
    {
        $xpath = new DOMXPath($dom);
        $node = $xpath->query("//*[@Id='{$id}']")->item(0);

        if (!$node instanceof DOMElement) {
            throw new RuntimeException("Id {$id} não encontrado no XML.");
        }

        return $node;
    }

    private function calculateDigest(DOMElement $node, XmlSignatureConfig $config): string
    {
        $canonical = $node->C14N(false, false);

        if ($canonical === false) {
            throw new RuntimeException('Falha ao canonicalizar nó de referência.');
        }

        $algo = $this->resolveDigestAlgorithm($config->digestMethodUri);

        return base64_encode(hash($algo, $canonical, true));
    }

    private function resolveDigestAlgorithm(string $digestMethodUri): string
    {
        return match ($digestMethodUri) {
            'http://www.w3.org/2001/04/xmlenc#sha256' => 'sha256',
            'http://www.w3.org/2000/09/xmldsig#sha1' => 'sha1',
            default => throw new RuntimeException("DigestMethod não suportado: {$digestMethodUri}"),
        };
    }

    private function buildSignature(
        DOMDocument $dom,
        string $referenceId,
        string $digestValue,
        string $certificatePem,
        XmlSignatureConfig $config
    ): DOMElement {
        $signature = $dom->createElementNS(self::XMLDSIG_NS, 'Signature');

        $signedInfo = $dom->createElementNS(self::XMLDSIG_NS, 'SignedInfo');
        $signature->appendChild($signedInfo);

        $c14n = $dom->createElementNS(self::XMLDSIG_NS, 'CanonicalizationMethod');
        $c14n->setAttribute('Algorithm', $config->canonicalizationMethod);
        $signedInfo->appendChild($c14n);

        $sigMethod = $dom->createElementNS(self::XMLDSIG_NS, 'SignatureMethod');
        $sigMethod->setAttribute('Algorithm', $config->signatureMethodUri);
        $signedInfo->appendChild($sigMethod);

        $reference = $dom->createElementNS(self::XMLDSIG_NS, 'Reference');
        $reference->setAttribute('URI', '#' . $referenceId);
        $signedInfo->appendChild($reference);

        $transforms = $dom->createElementNS(self::XMLDSIG_NS, 'Transforms');

        foreach ($config->transforms as $transform) {
            $t = $dom->createElementNS(self::XMLDSIG_NS, 'Transform');
            $t->setAttribute('Algorithm', $transform);
            $transforms->appendChild($t);
        }

        $reference->appendChild($transforms);

        $digestMethod = $dom->createElementNS(self::XMLDSIG_NS, 'DigestMethod');
        $digestMethod->setAttribute('Algorithm', $config->digestMethodUri);
        $reference->appendChild($digestMethod);

        $digest = $dom->createElementNS(self::XMLDSIG_NS, 'DigestValue', $digestValue);
        $reference->appendChild($digest);

        $signature->appendChild(
            $dom->createElementNS(self::XMLDSIG_NS, 'SignatureValue')
        );

        $keyInfo = $dom->createElementNS(self::XMLDSIG_NS, 'KeyInfo');
        $signature->appendChild($keyInfo);

        $x509Data = $dom->createElementNS(self::XMLDSIG_NS, 'X509Data');
        $keyInfo->appendChild($x509Data);

        $cert = $dom->createElementNS(
            self::XMLDSIG_NS,
            'X509Certificate',
            $this->normalizeCert($certificatePem)
        );

        $x509Data->appendChild($cert);

        return $signature;
    }

    private function signData(string $data, string $privateKeyPem, int $algorithm): string
    {
        $key = openssl_pkey_get_private($privateKeyPem);

        if (!$key) {
            throw new RuntimeException('Erro ao carregar chave privada.');
        }

        $signature = null;
        $ok = openssl_sign($data, $signature, $key, $algorithm);

        if ($ok !== true || !is_string($signature)) {
            throw new RuntimeException('Erro ao assinar os dados do XML.');
        }

        return base64_encode($signature);
    }

    private function normalizeCert(string $pem): string
    {
        return trim(str_replace([
            '-----BEGIN CERTIFICATE-----',
            '-----END CERTIFICATE-----',
            "\n",
            "\r",
        ], '', $pem));
    }

    private function appendSignature(
        DOMDocument $dom,
        DOMElement $signature,
        XmlSignatureConfig $config
    ): void {
        $xpath = new DOMXPath($dom);
        $node = $xpath->query($config->appendSignatureToXPath)->item(0);

        if (!$node instanceof DOMElement) {
            throw new RuntimeException('XPath para inserção da assinatura não encontrado.');
        }

        $node->appendChild($signature);
    }
}

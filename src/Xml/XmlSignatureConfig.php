<?php

namespace BetoCampoy\Champs\Fiscal\Xml;

final class XmlSignatureConfig
{
    public function __construct(
        public readonly string $canonicalizationMethod,
        public readonly string $signatureMethodUri,
        public readonly int $opensslAlgorithm,
        public readonly string $digestMethodUri,
        public readonly array $transforms,
        public readonly string $appendSignatureToXPath = '/*'
    ) {
    }

    public static function default(): self
    {
        return new self(
            canonicalizationMethod: 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315',
            signatureMethodUri: 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
            opensslAlgorithm: OPENSSL_ALGO_SHA1,
            digestMethodUri: 'http://www.w3.org/2000/09/xmldsig#sha1',
            transforms: [
                'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                'http://www.w3.org/TR/2001/REC-xml-c14n-20010315',
            ],
            appendSignatureToXPath: '/*'
        );
    }
}

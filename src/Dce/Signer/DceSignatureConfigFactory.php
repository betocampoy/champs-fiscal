<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Signer;

use BetoCampoy\Champs\Fiscal\Xml\XmlSignatureConfig;

final class DceSignatureConfigFactory
{
    public static function makeForAuthorization(): XmlSignatureConfig
    {
        return new XmlSignatureConfig(
            canonicalizationMethod: self::C14N,
            signatureMethodUri: self::SIGNATURE_METHOD,
            opensslAlgorithm: self::OPENSSL_ALGO,
            digestMethodUri: self::DIGEST_METHOD,
            transforms: self::TRANSFORMS,
            appendSignatureToXPath: '/*'
        );
    }

    public static function makeForCancelEvent(): XmlSignatureConfig
    {
        return new XmlSignatureConfig(
            canonicalizationMethod: self::C14N,
            signatureMethodUri: self::SIGNATURE_METHOD,
            opensslAlgorithm: self::OPENSSL_ALGO,
            digestMethodUri: self::DIGEST_METHOD,
            transforms: self::TRANSFORMS,
            appendSignatureToXPath: '/*'
        );
    }

    private const C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';

    private const SIGNATURE_METHOD = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';

    private const DIGEST_METHOD = 'http://www.w3.org/2000/09/xmldsig#sha1';

    private const OPENSSL_ALGO = OPENSSL_ALGO_SHA1;

    private const TRANSFORMS = [
        'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
        'http://www.w3.org/TR/2001/REC-xml-c14n-20010315',
    ];
}

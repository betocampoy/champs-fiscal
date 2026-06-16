<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Signer;

use BetoCampoy\Champs\Fiscal\Xml\XmlSignatureConfig;

final class NfseSignatureConfigFactory
{
    public static function makeForDps(): XmlSignatureConfig
    {
        return new XmlSignatureConfig(
            canonicalizationMethod: 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315',
            signatureMethodUri: 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
            opensslAlgorithm: OPENSSL_ALGO_SHA256,
            digestMethodUri: 'http://www.w3.org/2001/04/xmlenc#sha256',
            transforms: [
                'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                'http://www.w3.org/TR/2001/REC-xml-c14n-20010315',
            ],
            appendSignatureToXPath: '/*',
        );
    }
}

<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Signer;

use BetoCampoy\Champs\Fiscal\Xml\XmlSigner;
use BetoCampoy\Champs\Fiscal\Xml\XmlSignatureConfig;

final class DceSigner
{
    public function __construct(
        private readonly XmlSigner $xmlSigner,
    ) {
    }

    public function sign(
        string $xml,
        string $referenceId,
        string $privateKeyPem,
        string $certificatePem,
        XmlSignatureConfig $config,
    ): string {
        return $this->xmlSigner->sign(
            xml: $xml,
            referenceId: $referenceId,
            privateKeyPem: $privateKeyPem,
            certificatePem: $certificatePem,
            config: $config,
        );
    }
}

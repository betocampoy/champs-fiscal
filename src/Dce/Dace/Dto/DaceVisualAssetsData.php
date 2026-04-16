<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Dto;

final class DaceVisualAssetsData
{
    public function __construct(
        private readonly string $barcodeValue,          // chave de acesso (44)
        private readonly string $barcodeImageBase64,    // imagem pronta

        private readonly string $qrCodeValue,           // URL completa
        private readonly string $qrCodeImageBase64      // imagem pronta
    ) {
    }

    public function getBarcodeValue(): string
    {
        return $this->barcodeValue;
    }

    public function getBarcodeImageBase64(): string
    {
        return $this->barcodeImageBase64;
    }

    public function getQrCodeValue(): string
    {
        return $this->qrCodeValue;
    }

    public function getQrCodeImageBase64(): string
    {
        return $this->qrCodeImageBase64;
    }
}

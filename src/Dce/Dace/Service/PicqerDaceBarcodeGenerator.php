<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Service;

use BetoCampoy\Champs\Fiscal\Dce\Dace\Contract\DaceBarcodeGeneratorInterface;
use InvalidArgumentException;
use Picqer\Barcode\Renderers\PngRenderer;
use Picqer\Barcode\Types\TypeCode128;

final class PicqerDaceBarcodeGenerator implements DaceBarcodeGeneratorInterface
{
    public function __construct(
        private readonly float $width = 380, // largura total em px
        private readonly float $height = 80,
    ) {
    }

    public function generateBase64(string $content): string
    {
        $numbersOnly = preg_replace('/\D+/', '', $content) ?? '';

        if ($numbersOnly === '') {
            throw new InvalidArgumentException('O conteúdo do código de barras não pode ser vazio.');
        }

        $barcode = (new TypeCode128())->getBarcode($numbersOnly);

        $renderer = new PngRenderer();

        $binary = $renderer->render(
            $barcode,
            $this->width,
            $this->height
        );

        return base64_encode($binary);
    }
}

<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Service;

use BetoCampoy\Champs\Fiscal\Dce\Dace\Contract\DaceQrCodeGeneratorInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

final class EndroidDaceQrCodeGenerator implements DaceQrCodeGeneratorInterface
{
    public function __construct(
        private readonly int $size = 220,
        private readonly int $margin = 8,
    ) {
    }

    public function generateBase64(string $content): string
    {
        $writer = new PngWriter();

        $qrCode = new QrCode(
            data: $content,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: $this->size,
            margin: $this->margin,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $result = $writer->write($qrCode);

        return base64_encode($result->getString());
    }
}

<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

class QrGeneratorService
{
    private const SIZE = 300;

    public function generar(string $contenido): string
    {
        $result = (new Builder())->build(
            writer: new PngWriter(),
            data: $contenido,
            encoding: new Encoding('UTF-8'),
            size: self::SIZE,
            margin: 1,
        );

        return base64_encode($result->getString());
    }
}

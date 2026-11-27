<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeSvgService
{
    public function generateInlineSvg(string $content, int $size = 110, int $margin = 1): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, $margin),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($content);

        // Strip XML header so SVG can be embedded cleanly inside HTML5 documents.
        return preg_replace('/<\?xml.*?\?>\s*/', '', $svg) ?: $svg;
    }
}

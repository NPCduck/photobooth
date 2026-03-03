<?php

namespace App\Utils;

use Imagick;
use ImagickDraw;
use ImagickPixel;

class SvgPhotoClipper
{
    private $imagick;
    private $svgContent;
    private $bleedMm = 3;
    private $dpi = 300; // Standard print DPI

    public function __construct(string $svgContent, int $bleedMm = 3, int $dpi = 300)
    {
        $this->svgContent = $svgContent;
        $this->bleedMm = $bleedMm;
        $this->dpi = $dpi;
    }

    /**
     * Clip image to SVG shape with bleed
     * 
     * @param string $imagePath Path to the image file
     * @return Imagick Clipped image
     */
    public function clipImage(string $imagePath): Imagick
    {
        $this->imagick = new Imagick($imagePath);
        
        // Get SVG dimensions
        $svgDimensions = $this->parseSvgDimensions();
        
        // Calculate bleed in pixels
        $bleedPx = $this->mmToPixels($this->bleedMm);
        
        // Resize image to match SVG dimensions with bleed
        $this->resizeImageToSvg($svgDimensions, $bleedPx);
        
        // Create clipping path from SVG
        $this->applyClippingPath();
        
        return $this->imagick;
    }

    /**
     * Parse SVG viewBox and width/height attributes
     */
    private function parseSvgDimensions(): array
    {
        $svg = simplexml_load_string($this->svgContent);
        
        $viewBox = (string)$svg['viewBox'];
        $width = (string)$svg['width'];
        $height = (string)$svg['height'];
        
        $dimensions = [
            'width' => $this->parseLength($width),
            'height' => $this->parseLength($height),
        ];
        
        // If no explicit width/height, try viewBox
        if (!$dimensions['width'] || !$dimensions['height']) {
            if ($viewBox) {
                $parts = explode(' ', $viewBox);
                if (count($parts) >= 4) {
                    $dimensions['width'] = (float)$parts[2];
                    $dimensions['height'] = (float)$parts[3];
                }
            }
        }
        
        return $dimensions;
    }

    /**
     * Parse SVG length values (support px, mm, cm, in, etc.)
     */
    private function parseLength(string $value): ?float
    {
        if (empty($value)) {
            return null;
        }
        
        $value = trim($value);
        
        // Extract number and unit
        if (preg_match('/^([\d.]+)(px|mm|cm|in|pt|pc)?$/', $value, $matches)) {
            $number = (float)$matches[1];
            $unit = $matches[2] ?? 'px';
            
            // Convert to pixels (assuming 96 DPI for screen, 300 DPI for print)
            switch ($unit) {
                case 'mm':
                    return $this->mmToPixels($number);
                case 'cm':
                    return $this->mmToPixels($number * 10);
                case 'in':
                    return $this->mmToPixels($number * 25.4);
                case 'pt':
                    return $number * (96 / 72); // points to pixels
                case 'pc':
                    return $number * 16; // pica to pixels
                case 'px':
                default:
                    return $number;
            }
        }
        
        return null;
    }

    /**
     * Convert millimeters to pixels based on DPI
     */
    private function mmToPixels(float $mm): float
    {
        return ($mm / 25.4) * $this->dpi;
    }

    /**
     * Resize image to match SVG dimensions with bleed
     */
    private function resizeImageToSvg(array $svgDimensions, float $bleedPx): void
    {
        if (!$svgDimensions['width'] || !$svgDimensions['height']) {
            throw new \Exception('Unable to determine SVG dimensions');
        }
        
        // Calculate target size with bleed (2x bleed on each dimension)
        $targetWidth = $svgDimensions['width'] + (2 * $bleedPx);
        $targetHeight = $svgDimensions['height'] + (2 * $bleedPx);
        
        // Resize and crop image to fit target dimensions
        $this->imagick->resizeImage(
            $targetWidth,
            $targetHeight,
            Imagick::FILTER_LANCZOS,
            1,
            true
        );
    }

    /**
     * Apply clipping path from SVG shape
     * This creates a mask based on the SVG geometry
     */
    private function applyClippingPath(): void
    {
        // Create a mask/clip mask from the SVG
        // This is a simplified approach - for complex SVGs, you may need a library like svglib
        
        $svg = simplexml_load_string($this->svgContent);
        
        // Create drawing context
        $draw = new ImagickDraw();
        $bleedPx = $this->mmToPixels($this->bleedMm);
        
        // Set fill color for the clipping path
        $draw->setFillOpacity(1.0);
        $draw->setFillColor(new ImagickPixel('black'));
        
        // Process SVG paths and shapes
        $this->processSvgShapes($svg, $draw, $bleedPx);
        
        // Create a clone for the mask
        $mask = new Imagick();
        $mask->newImage(
            $this->imagick->getImageWidth(),
            $this->imagick->getImageHeight(),
            new ImagickPixel('white')
        );
        $mask->setImageFormat('png');
        $mask->drawImage($draw);
        
        // Apply the mask
        $this->imagick->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);
    }

    /**
     * Process SVG shapes (circles, rectangles, polygons, paths)
     * These get rendered to the mask
     */
    private function processSvgShapes($svg, ImagickDraw $draw, float $bleedPx): void
    {
        // Register SVG namespace
        $namespaces = $svg->getDocNamespaces();
        $svg->registerXPathNamespace('svg', 'http://www.w3.org/2000/svg');
        
        // Process circles
        foreach ($svg->xpath('//svg:circle | //circle') as $circle) {
            $cx = (float)$circle['cx'] + $bleedPx;
            $cy = (float)$circle['cy'] + $bleedPx;
            $r = (float)$circle['r'];
            
            $draw->circle($cx, $cy, $cx + $r, $cy);
        }
        
        // Process rectangles
        foreach ($svg->xpath('//svg:rect | //rect') as $rect) {
            $x = (float)$rect['x'] + $bleedPx;
            $y = (float)$rect['y'] + $bleedPx;
            $width = (float)$rect['width'];
            $height = (float)$rect['height'];
            
            $rx = isset($rect['rx']) ? (float)$rect['rx'] : 0;
            
            if ($rx > 0) {
                $draw->roundRectangle($x, $y, $x + $width, $y + $height, $rx, $rx);
            } else {
                $draw->rectangle($x, $y, $x + $width, $y + $height);
            }
        }
        
        // Process polygons
        foreach ($svg->xpath('//svg:polygon | //polygon') as $polygon) {
            $points = (string)$polygon['points'];
            $pointsArray = $this->parsePoints($points, $bleedPx);
            
            if (!empty($pointsArray)) {
                $draw->polygon($pointsArray);
            }
        }
        
        // Process polylines
        foreach ($svg->xpath('//svg:polyline | //polyline') as $polyline) {
            $points = (string)$polyline['points'];
            $pointsArray = $this->parsePoints($points, $bleedPx);
            
            if (!empty($pointsArray)) {
                $draw->polyline($pointsArray);
            }
        }
        
        // Process paths (simplified - basic path support)
        foreach ($svg->xpath('//svg:path | //path') as $path) {
            $d = (string)$path['d'];
            // For complex paths, you might want to use a library like svg-path-parser
            // For now, we'll skip complex path processing
        }
    }

    /**
     * Parse SVG points string "x1,y1 x2,y2 ..."
     */
    private function parsePoints(string $pointsStr, float $bleedPx): array
    {
        $points = [];
        $pairs = preg_split('/[\s,]+/', trim($pointsStr));
        
        for ($i = 0; $i < count($pairs) - 1; $i += 2) {
            if (is_numeric($pairs[$i]) && is_numeric($pairs[$i + 1])) {
                $points[] = [
                    'x' => (float)$pairs[$i] + $bleedPx,
                    'y' => (float)$pairs[$i + 1] + $bleedPx,
                ];
            }
        }
        
        return $points;
    }

    /**
     * Save clipped image to file
     */
    public function saveToFile(string $outputPath, string $format = 'jpeg', int $quality = 95): void
    {
        $this->imagick->setFormat($format);
        $this->imagick->setCompressionQuality($quality);
        $this->imagick->writeImage($outputPath);
    }

    /**
     * Get image as data
     */
    public function getImageData(string $format = 'jpeg', int $quality = 95): string
    {
        $this->imagick->setFormat($format);
        $this->imagick->setCompressionQuality($quality);
        return $this->imagick->getImageBlob();
    }
}

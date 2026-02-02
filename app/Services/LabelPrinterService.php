<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LabelPrinterService
{
    protected $printerName = 'Brother_QL_820NWB';

    /**
     * Print a QR code label.
     *
     * @param string $content The content of the QR code.
     * @param array $metadata Additional text to print (e.g., ID, date).
     * @param int $count Number of copies.
     * @return bool True if successful, false otherwise.
     */
    public function printQrCode(string $content, array $metadata = [], int $count = 1): bool
    {
        if ($count < 1) {
            return false;
        }

        // Validate printer name
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $this->printerName)) {
            Log::error("Invalid printer name: {$this->printerName}");
            return false;
        }

        $tempDir = storage_path('app/temp/labels');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filename = $tempDir . '/' . Str::random(16) . '.png';

        try {
            // 1. Generate QR Code raw data (PNG format)
            // We use a small size here, we will resize/place it on canvas later or just use it as is.
            $qrImageString = QrCode::format('png')
                  ->size(300)
                  ->margin(1)
                  ->errorCorrection('H')
                  ->generate($content);

            // 2. Create GD Resource from QR Code
            $qrImage = imagecreatefromstring($qrImageString);
            $qrWidth = imagesx($qrImage);
            $qrHeight = imagesy($qrImage);

            // 3. Create Label Canvas
            // Standard Address Label approx ratio (e.g., 62mm x 29mm or similar continuous)
            // Let's create a canvas that fits QR + Text.
            // Width: 320, Height: 400 (QR 300 + 100 text)
            $canvasWidth = $qrWidth + 20; // 10px padding
            $canvasHeight = $qrHeight + 120; // Room for text

            $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
            $white = imagecolorallocate($canvas, 255, 255, 255);
            $black = imagecolorallocate($canvas, 0, 0, 0);

            // Return false if color allocation fails
            if ($white === false || $black === false) {
                 throw new \Exception("Could not allocate colors.");
            }

            imagefilledrectangle($canvas, 0, 0, $canvasWidth, $canvasHeight, $white);

            // 4. Copy QR to Canvas (centered horizontally)
            imagecopy($canvas, $qrImage, 10, 10, 0, 0, $qrWidth, $qrHeight);

            // 5. Add Text
            // Font: built-in generic font (1 to 5)
            $font = 5; 
            $yOffset = $qrHeight + 20;
            
            // Text 1: Document Key
            $text1 = $metadata['title'] ?? 'Doklad: ' . $content;
            $text1Width = imagefontwidth($font) * strlen($text1);
            $x1 = ($canvasWidth - $text1Width) / 2;
            imagestring($canvas, $font, (int)$x1, $yOffset, $text1, $black);

            // Text 2: Date
            $text2 = $metadata['date'] ?? date('d.m.Y H:i');
            $text2Width = imagefontwidth($font) * strlen($text2);
            $x2 = ($canvasWidth - $text2Width) / 2;
            imagestring($canvas, $font, (int)$x2, $yOffset + 20, $text2, $black);
            
            // Text 3: Note (optional)
            if (!empty($metadata['note'])) {
                 $text3 = $metadata['note'];
                 $text3Width = imagefontwidth($font) * strlen($text3);
                 $x3 = ($canvasWidth - $text3Width) / 2;
                 imagestring($canvas, $font, (int)$x3, $yOffset + 40, $text3, $black);
            }

            // 6. Save Final Image
            imagepng($canvas, $filename);

            // Cleanup GD resources
            imagedestroy($qrImage);
            imagedestroy($canvas);

            // 7. Print
            // Standard local printing command.
            // On the production server, the printer must be installed and named correctly in CUPS.
            $cmd = sprintf(
                'lp -d %s -n %d %s 2>&1',
                escapeshellarg($this->printerName),
                $count,
                escapeshellarg($filename)
            );

            Log::info("Printing label command: $cmd");

            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0) {
                Log::error("Printing failed. Return code: $returnVar", ['output' => $output]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error printing label: " . $e->getMessage());
            return false;
        } finally {
            if (file_exists($filename)) {
                // unlink($filename); // Commented out for debugging if needed, but should be uncommented in prod
                 unlink($filename);
            }
        }
    }
}

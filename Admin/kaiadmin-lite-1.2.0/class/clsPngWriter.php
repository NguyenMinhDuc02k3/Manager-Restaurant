<?php
/**
 * Simple PNG Writer Class for QR Codes
 */
class PngWriter {
    /**
     * Write the QR code to a string
     * 
     * @param QRCode $qrCode The QR code to write
     * @return Result The result object that contains the QR code data
     */
    public function write($qrCode) {
        error_log("PngWriter: Writing QR code to string");
        return new Result($qrCode);
    }
}

/**
 * Result class to hold the QR code data
 */
class Result {
    private $qrCode;
    
    /**
     * Constructor
     * 
     * @param QRCode $qrCode The QR code
     */
    public function __construct($qrCode) {
        error_log("Result: Constructing with QR code");
        $this->qrCode = $qrCode;
    }
    
    /**
     * Get the QR code data as a string
     * 
     * @return string The QR code data
     */
    public function getString() {
        error_log("Result: Getting QR code as string");
        try {
            // Get the data URI from the QR code
            $dataUri = $this->qrCode->getDataUri();
            error_log("Result: Data URI received, length: " . strlen($dataUri));
            
            // Remove the data URI prefix to get just the base64 encoded data
            $base64Data = str_replace('data:image/png;base64,', '', $dataUri);
            if (empty($base64Data)) {
                error_log("WARNING: Base64 data is empty after removing prefix");
            } else {
                error_log("Result: Base64 data length: " . strlen($base64Data));
            }
            
            // Decode the base64 data
            $decodedData = base64_decode($base64Data);
            if ($decodedData === false) {
                error_log("ERROR: Failed to decode base64 data");
                throw new Exception("Failed to decode base64 data");
            }
            
            error_log("Result: Decoded data length: " . strlen($decodedData));
            return $decodedData;
        } catch (Exception $e) {
            error_log("Error in Result::getString(): " . $e->getMessage());
            // Return an empty PNG as fallback
            return $this->generateEmptyPng();
        }
    }
    
    /**
     * Generate an empty PNG image as fallback
     * 
     * @return string PNG image data
     */
    private function generateEmptyPng() {
        error_log("Generating empty PNG as fallback");
        if (extension_loaded('gd')) {
            $im = imagecreatetruecolor(200, 200);
            $bgColor = imagecolorallocate($im, 255, 255, 255);
            $textColor = imagecolorallocate($im, 0, 0, 0);
            imagefilledrectangle($im, 0, 0, 200, 200, $bgColor);
            imagestring($im, 5, 10, 90, 'Scan to pay', $textColor);
            
            ob_start();
            imagepng($im);
            $imageData = ob_get_clean();
            imagedestroy($im);
            
            error_log("Fallback PNG generated: " . strlen($imageData) . " bytes");
            return $imageData;
        } else {
            error_log("GD not available for fallback image");
            // Return an empty string if GD is not available
            return '';
        }
    }
} 
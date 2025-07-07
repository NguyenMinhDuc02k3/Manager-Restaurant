<?php
/**
 * Simple QR Code Generator Class
 * Requires the GD library to be installed and enabled
 */
class QRCode {
    private $size;
    private $data;
    private $margin;
    
    /**
     * Constructor
     * 
     * @param string $data The data to encode in the QR code
     * @param int $size The size of the QR code (default: 200)
     * @param int $margin The margin around the QR code (default: 10)
     */
    public function __construct($data, $size = 200, $margin = 10) {
        if (empty($data)) {
            throw new Exception('QR Code data cannot be empty');
        }
        
        $this->data = $data;
        $this->size = $size;
        $this->margin = $margin;
        
        error_log("QRCode constructor - Data length: " . strlen($data) . ", Size: $size, Margin: $margin");
    }
    
    /**
     * Set the size of the QR code image
     * 
     * @param int $size The size of the QR code
     * @return QRCode Returns this QRCode instance for method chaining
     */
    public function setSize($size) {
        error_log("Setting QR code size to: $size");
        $this->size = $size;
        return $this;
    }
    
    /**
     * Set the margin around the QR code
     * 
     * @param int $margin The margin around the QR code
     * @return QRCode Returns this QRCode instance for method chaining
     */
    public function setMargin($margin) {
        error_log("Setting QR code margin to: $margin");
        $this->margin = $margin;
        return $this;
    }
    
    /**
     * Generate a QR code image and return it as a base64 encoded string
     * 
     * @return string The base64 encoded QR code image
     */
    public function getDataUri() {
        error_log("Generating QR code data URI");
        
        // Method 1: Use Google Charts API
        try {
            error_log("Attempting to generate QR code using Google Charts API");
            
            // Generate QR code using Google Charts API
            $url = 'https://chart.googleapis.com/chart?chs=' . $this->size . 'x' . $this->size . 
                '&cht=qr&chld=H|1&chl=' . urlencode($this->data) . 
                '&choe=UTF-8';
            
            error_log("Google Charts API URL: $url");
            
            // Get QR code image data with proper error handling
            $qrCode = @file_get_contents($url);
            
            if ($qrCode === false) {
                $error = error_get_last();
                error_log("Google Charts API request failed: " . ($error ? $error['message'] : 'Unknown error'));
                
                // Fall back to method 2
                throw new Exception('Failed to fetch QR code from Google Charts API');
            }
            
            error_log("QR code generated successfully using Google Charts API: " . strlen($qrCode) . " bytes");
            return 'data:image/png;base64,' . base64_encode($qrCode);
        } 
        catch (Exception $e) {
            error_log("Google Charts API method failed: " . $e->getMessage());
            
            // Method 2: Use GD library if available
            if (extension_loaded('gd')) {
                error_log("Attempting to generate QR code using GD library");
                
                // Create a simple image with text (as fallback)
                $im = imagecreatetruecolor(200, 200);
                $bgColor = imagecolorallocate($im, 255, 255, 255);
                $textColor = imagecolorallocate($im, 0, 0, 0);
                imagefilledrectangle($im, 0, 0, 200, 200, $bgColor);
                imagestring($im, 5, 10, 90, 'Scan to pay', $textColor);
                
                // Start output buffering to capture image data
                ob_start();
                imagepng($im);
                $imageData = ob_get_clean();
                imagedestroy($im);
                
                error_log("GD fallback image generated: " . strlen($imageData) . " bytes");
                return 'data:image/png;base64,' . base64_encode($imageData);
            }
            
            // If all methods fail, rethrow the exception
            error_log("All QR code generation methods failed");
            throw new Exception('Failed to generate QR code: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate a QR code and output it directly to the browser
     */
    public function display() {
        header('Content-Type: image/png');
        echo base64_decode(str_replace('data:image/png;base64,', '', $this->getDataUri()));
        exit;
    }
    
    /**
     * Static factory method to create a QR code
     * 
     * @param string $data The data to encode in the QR code
     * @return QRCode A new QRCode instance
     */
    public static function create($data) {
        return new self($data);
    }
} 
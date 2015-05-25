<?php
class PhpBarcode_Code39 extends PhpBarcode
{
    /**
     * Barcode type
     * @var string
     */
    public $_type = 'Code39';

    /**
     * Barcode height
     *
     * @var integer
     */
    public $_barcodeheight = 50;

    /**
     * Bar thin width
     *
     * @var integer
     */
    public $_barthinwidth = 1;

    /**
     * Bar thick width
     *
     * @var integer
     */
    public $_barthickwidth = 3;
    
    public $textPos; //show the text below the barcode or above the barcode
    
    public static $TextPos_Below= "below";
    public static $TextPos_Above= "above";

    /**
     * Coding map
     * @var array
     */
    public $_coding_map = array(
        '0' => '000110100',
        '1' => '100100001',
        '2' => '001100001',
        '3' => '101100000',
        '4' => '000110001',
        '5' => '100110000',
        '6' => '001110000',
        '7' => '000100101',
        '8' => '100100100',
        '9' => '001100100',
        'A' => '100001001',
        'B' => '001001001',
        'C' => '101001000',
        'D' => '000011001',
        'E' => '100011000',
        'F' => '001011000',
        'G' => '000001101',
        'H' => '100001100',
        'I' => '001001100',
        'J' => '000011100',
        'K' => '100000011',
        'L' => '001000011',
        'M' => '101000010',
        'N' => '000010011',
        'O' => '100010010',
        'P' => '001010010',
        'Q' => '000000111',
        'R' => '100000110',
        'S' => '001000110',
        'T' => '000010110',
        'U' => '110000001',
        'V' => '011000001',
        'W' => '111000000',
        'X' => '010010001',
        'Y' => '110010000',
        'Z' => '011010000',
        '-' => '010000101',
        '*' => '010010100',
        '+' => '010001010',
        '$' => '010101000',
        '%' => '000101010',
        '/' => '010100010',
        '.' => '110000100',
        ' ' => '011000100'
    );

    /**
     * Constructor
     *
     * @param  string $text     A text that should be in the image barcode
     * @param  int $wThin       Width of the thin lines on the barcode
     * @param  int $wThick      Width of the thick lines on the barcode
     *
     */
    public function __construct($text = '', $textPos="",$wThin = 0, $wThick = 0 )
    {
        // Check $text for invalid characters
        $text = strtoupper(trim($text));
        if (!$this->checkInvalid( $text )) 
            throw new Exception('Invalid text');

        $this->text = $text;
        if ( $wThin > 0 ) $this->_barthinwidth = $wThin;
        if ( $wThick > 0 ) $this->_barthickwidth = $wThick;
        
        //only change position when it requires above; otherwise, stay below
        $this->textPos = (trim($textPos) != PhpBarcode_Code39::$TextPos_Above) ? PhpBarcode_Code39::$TextPos_Below : PhpBarcode_Code39::$TextPos_Above;
    }

   /**
    * Make an image resource using the GD image library
    *
    * @param    bool $noText       Set to true if you'd like your barcode to be sans text
    * @param    int $bHeight       height of the barcode image including text
    * @return   resource           The Barcode Image (TM)
    *
    */
    public function plot($noText = false, $bHeight = 0)
    {
       // add start and stop * characters
       $final_text = '*' . $this->text . '*';

        if ( $bHeight > 0 ) 
            $this->_barcodeheight = $bHeight;

       $barcode = '';
       $final_text =strtoupper($final_text);
       foreach ( str_split( $final_text ) as $character ) 
       {
           $barcode .= $this->_dumpCode( $this->_coding_map[$character] . '0' );
       }

       $barcode_len = strlen( $barcode );
       
       // Create GD image object
       $img = imagecreate( $barcode_len, $this->_barcodeheight );

       // Allocate black and white colors to the image
       $black = imagecolorallocate( $img, 0, 0, 0 );
       $white = imagecolorallocate( $img, 255, 255, 255 );
       $font_height = ( $noText ? 0 : imagefontheight( 4 ) );
       $font_width = imagefontwidth( 4 );

       // fill background with white color
       imagefill( $img, 0, 0, $white );

       // Initialize X position
       $xpos = 0;
	   $ypos = ($this->textPos==PhpBarcode_Code39::$TextPos_Above) ? ($font_height + 1) : 0;
       // draw barcode bars to image
        if ( $noText ) 
        {
            foreach (str_split($barcode) as $character_code) 
            {
                if ($character_code == 0) 
                    imageline($img, $xpos, $ypos, $xpos, $this->_barcodeheight, $white);
                else 
                    imageline($img, $xpos, $ypos, $xpos, $this->_barcodeheight, $black);

                $xpos++;
            }
        } 
        else 
        {
            foreach (str_split($barcode) as $character_code) 
            {
                if ($character_code == 0) 
                    imageline($img, $xpos, $ypos, $xpos, $this->_barcodeheight - $font_height - 1, $white);
                else
                    imageline($img, $xpos, $ypos, $xpos, $this->_barcodeheight - $font_height - 1, $black);
                $xpos++;
            }

            // draw text under barcode
            imagestring(
                $img,
                4,
                ( $barcode_len - $font_width * strlen( $this->text ) )/2,
                (($this->textPos==PhpBarcode_Code39::$TextPos_Above) ? 0: $this->_barcodeheight - $font_height),
                $this->text,
                $black
            );
        }

        return $img;
    }

    /**
     * Send image to the browser; for Image_Barcode compaitbility
     *
     * @param    string $text
     * @param    string $imgtype     Image type; accepts jpg, png, and gif, but gif only works if you've payed for licensing
     * @param    bool $noText        Set to true if you'd like your barcode to be sans text
     * @param    int $bHeight        height of the barcode image including text
     * @return   gd_image            GD image object
     *
     */
    public function draw($text, $noText = false,$imgtype = 'png', $bHeight = 0)
    {
        // Check $text for invalid characters
        $text = strtoupper(trim($text));
        if (!$this->checkInvalid($text)) 
           throw new Exception('Invalid text');
           
        $this->text = $text;
        $img = $this->plot($noText, $bHeight);

        return $img;
    }

    /**
     * _dumpCode is a PHP implementation of dumpCode from the Perl module
     * GD::Barcode::Code39. I royally screwed up when trying to do the thing
     * my own way the first time. This way works.
     *
     * @param   string $code        Code39 barcode code
     * @return  string $result      barcode line code
     *
     *
     *
     */
    private function _dumpCode($code)
    {
        $result = '';
        $color = 1; // 1: Black, 0: White

        // if $bit is 1, line is wide; if $bit is 0 line is thin
        foreach ( str_split( $code ) as $bit ) {
            $result .= ( ( $bit == 1 ) ? str_repeat( "$color", $this->_barthickwidth ) : str_repeat( "$color", $this->_barthinwidth ) );
            $color = ( ( $color == 0 ) ? 1 : 0 );
        }

        return $result;
    }

    /**
     * Check for invalid characters
     *
     * @param   string $text    text to be ckecked
     * @return  bool            returns true when invalid characters have been found
     */
    private function checkInvalid($text)
    {
    	$chars = str_split($text);
    	foreach($chars as $char)
    	{
    		if(!array_key_exists($char,$this->_coding_map))
    			return false;
    	}
       return true;
    }
}
?>
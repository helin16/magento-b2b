<?php
class PhpBarcode
{
    public static function drawBarcode($text, $noText = false, $textPos="", $debug = false)
    {
        try
        {
            $type = 'Code39';
            $textPos = (trim($textPos)=="") ?  PhpBarcode_Code39::$TextPos_Below : "";

            //Make sure no bad files are included
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $type))
                throw new Exception('Invalid barcode type ' . $type);
            if (!include_once(dirname(__FILE__).'/PhpBarcode_' . $type . '.php'))
               throw new Exception($type . ' barcode is not supported');

            $classname = 'PhpBarcode_' . $type;

            if (!in_array('draw',get_class_methods($classname)))
               throw new Exception("Unable to find draw method in '$classname' class");
            $text = strtoupper(trim($text));
            $obj = new $classname($text,$textPos);
            $img = $obj->draw($text,$noText, 'png');
            ob_start();
            imagepng($img);
            $image_data = ob_get_contents();
            ob_end_clean();
			$result =  array("filename" => md5($text) . 'png', 'content' => $image_data, 'mimeType' => 'image/png');
			imagedestroy($img);
			return $result;
        }
        catch(Exception $ex)
        {
                throw $ex;
        }
    }
}
?>
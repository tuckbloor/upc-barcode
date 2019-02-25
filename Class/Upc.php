<?php

namespace App;

class Upc {

    private $width;
    private $height;
    private $barcode_size;
    private $bar_height;
    private $border = 2;
    private $padding;
    private $manufacturer_code;
    private $product_code;
    private $code;
    private $barcode;

    function __construct($barcode_size, $code) {

        if (!extension_loaded('gd')) {
            echo "GD extension Is Not Available";
            return false;
        }

        $this->code = $code;
        $this->barcode_size = $barcode_size; //set the width of the bar
        $this->padding  = $this->barcode_size * 10;  //set padding around the barcode

        //left barcode odd number of 1 so that it can be scanned upside down (manufacturers code)
        $this->manufacturer_code = array(
            '0' => '0001101', '1' => '0011001', '2' => '0010011', '3' => '0111101', '4' => '0100011',
            '5' => '0110001', '6' => '0101111', '7' => '0111011', '8' => '0110111', '9' => '0001011',
            '#' => '01010', '*' => '101'
        );

        //right code even number of 1 so that the barcode can be scanned upside down (product code)
        $this->product_code = array(
            '0' => '1110010', '1' => '1100110', '2' => '1101100', '3' => '1000010', '4' => '1011100',
            '5' => '1001110', '6' => '1010000', '7' => '1000100', '8' => '1001000', '9' => '1110100',
            '#' => '01010', '*' => '101'
        );

    }

    public function __call($method, $args) {
        echo "Method " . $method . " Does Not Exist";
    }

    public function generate() {

        //check to see if code is numeric
        if(is_numeric($this->code) && strlen($this->code) == 12) {


            $font = 'Aller_Rg.ttf';

            //the numbers to display
            $first_digit            = substr($this->code,0,1);
            $left_manufacturer_code = substr($this->code,1,5);
            $right_product_code     = substr($this->code,6,5);
            $last_digit             = substr($this->code,11,1);

            $this->code = '*' . substr($this->code, 0, 6) . '#' . substr($this->code, 6, 6) . '*';

            //12 x 7 + 6 +5 = 95 # = 1 x 5 * = 2 x 3
            $this->width = $this->barcode_size * 95 + $this->padding * 2;
            $this->bar_height = $this->barcode_size * 95 * 0.75;
            $this->height = $this->bar_height + $this->padding * 2;

            $this->barcode = imagecreatetruecolor($this->width, $this->bar_height);

            $black = imagecolorallocate($this->barcode,0,0,0);
            $white = imagecolorallocate($this->barcode,255,255,255);

            imagefilledrectangle($this->barcode, $this->border, $this->border,$this->width - $this->border -1,
                                 $this->bar_height - $this->border -1, $white);

            $bar_start_position = $this->padding;

            //15 for 12 characters of the code and 2 * and 1 #
            for($count = 0; $count < 15; $count++) {

                $character = $this->code[$count];

                for($subcount = 0; $subcount < strlen($this->manufacturer_code[$character]); $subcount++) {
                    if($count < 7) {
                        $colour = ($this->manufacturer_code[$character][$subcount] == 0) ? $white : $black;
                    }
                    else {
                        $colour = ($this->product_code[$character][$subcount] == 0) ? $white : $black;
                    }

                    if(strlen($this->manufacturer_code[$character]) == 7) {
                        $height_offset = $this->height * 0.05;
                    } else {
                        $height_offset = 0;
                    }

                    imagefilledrectangle($this->barcode, $bar_start_position, $this->padding, $bar_start_position +
                             $this->barcode_size -1,$this->bar_height - $height_offset - $this->padding, $colour);

                    //move position for the next line
                    $bar_start_position += $this->barcode_size;

                }
            }


            //calculate the variables for the imagettftext
            $font_size = 8   * $this->barcode_size;
            $x1        = 2   * $this->barcode_size;
            $x2        = 18  * $this->barcode_size;
            $x3        = 64  * $this->barcode_size;
            $x4        = 106 * $this->barcode_size;
            $y         = 66  * $this->barcode_size;


            imagettftext ( $this->barcode , $font_size, 0 , $x1 , $y  , $black , $font , $first_digit );
            imagettftext ( $this->barcode , $font_size, 0 , $x2 , $y  , $black , $font , $left_manufacturer_code );
            imagettftext ( $this->barcode , $font_size, 0 , $x3 , $y  , $black , $font , $right_product_code );
            imagettftext ( $this->barcode , $font_size, 0 , $x4 , $y  , $black , $font , $last_digit );

            //display the barcode
            header("Content-type: image/png");
            imagepng($this->barcode);
            imagedestroy($this->barcode);
        }
        else {
            echo "The Code Entered Was Not A Valid Format";
        }
    }

}
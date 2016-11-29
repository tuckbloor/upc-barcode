<?php

class Upc {

    private $width;
    private $height;
    private $bar_width;
    private $bar_height;
    private $border;
    private $padding;
    private $manufacturer_code;
    private $product_code;
    private $code;

    function __construct($bar_width) {

        $this->bar_width = $bar_width; //set the width of the bar
        $this->padding  = $bar_width * 10;  //set padding around the barcode
        $this->border   = 2; //2px of border

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

    public function generate($code) {

        //the code passed
        $this->code = $code;

        //check to see if code is numeric
        if(is_numeric($this->code)) {

            $upc_code = $this->code;

            $this->code = '*' . substr($this->code, 0, 6) . '#' . substr($this->code, 6, 6) . '*';

            //12 x 7 + 6 +5 = 95 # = 1 x 5 * = 2 x 3
            $this->width = $this->bar_width * 95 + $this->padding * 2;
            $this->bar_height = $this->bar_width * 95 * 0.75;
            $this->height = $this->bar_height + $this->padding * 2;

            $barcode = imagecreatetruecolor($this->width, $this->bar_height);

            $black = imagecolorallocate($barcode,0,0,0);
            $white = imagecolorallocate($barcode,255,255,255);

            imagefilledrectangle($barcode, $this->border, $this->border,$this->width - $this->border -1,
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

                    imagefilledrectangle($barcode, $bar_start_position, $this->padding, $bar_start_position +
                             $this->bar_width -1,$this->bar_height - $height_offset - $this->padding, $colour);

                    //move position for the next line
                    $bar_start_position += $this->bar_width;

                }
            }

            $font = 'Aller_Rg.ttf';

            //the numbers to display
            $first_digit            = substr($upc_code,0,1);
            $left_manufacturer_code = substr($upc_code,1,5);
            $right_product_code     = substr($upc_code,6,5);
            $last_digit             = substr($upc_code,11,1);

            //calculate the variables for the imagettftext
            $font_size = 8   * $this->bar_width;
            $x1        = 2   * $this->bar_width;
            $x2        = 18  * $this->bar_width;
            $x3        = 64  * $this->bar_width;
            $x4        = 106 * $this->bar_width;
            $y         = 66  * $this->bar_width;


            imagettftext ( $barcode , $font_size, 0 , $x1 , $y  , $black , $font , $first_digit );
            imagettftext ( $barcode , $font_size, 0 , $x2 , $y  , $black , $font , $left_manufacturer_code );
            imagettftext ( $barcode , $font_size, 0 , $x3 , $y  , $black , $font , $right_product_code );
            imagettftext ( $barcode , $font_size, 0 , $x4 , $y  , $black , $font , $last_digit );

            //display the barcode
            header("Content-type: image/png");
            imagepng($barcode);
            imagedestroy($barcode);
        }
    }
}
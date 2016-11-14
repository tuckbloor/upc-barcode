<?php

class Upc {

    private $width;
    private $height;
    private $barwidth;
    private $barheight;
    private $border;
    private $padding;
    private $left_side_code;
    private $right_side_code;
    private $code;

    function __construct($barwidth) {

        $this->barwidth = $barwidth; //set the width of the border
        $this->padding  = $barwidth * 10;  //set padding around the barcode
        $this->border   = 2; //2px of border

        //left barcode odd number of 1 so that it can be scanned upside down
        $this->left_side_code = array(
            '0' => '0001101', '1' => '0011001', '2' => '0010011', '3' => '0111101', '4' => '0100011',
            '5' => '0110001', '6' => '0101111', '7' => '0111011', '8' => '0110111', '9' => '0001011',
            '#' => '01010', '*' => '101'
        );

        //right code even number of 1 so that the barcode can be scanned upsidedown
        $this->right_side_code = array(
            '0' => '1110010', '1' => '1100110', '2' => '1101100', '3' => '1000010', '4' => '1011100',
            '5' => '1001110', '6' => '1010000', '7' => '1000100', '8' => '1001000', '9' => '1110100',
            '#' => '01010', '*' => '101'
        );

    }

    public function build($code) {

        //the code passed
        $this->code = $code;

        //check to see if code is numeric
        if(is_numeric($code)) {

            $upc_code = $code;

            $code = '*' . substr($code, 0, 6) . '#' . substr($code, 6, 6) . '*';

            $this->width = $this->barwidth * 95 + $this->padding * 2;
            $this->barheight = $this->barwidth * 95 * 0.75;
            $this->height = $this->barheight + $this->padding * 2;

            $barcode = imagecreatetruecolor($this->width, $this->barheight);

            $black = imagecolorallocate($barcode,0,0,0);
            $white = imagecolorallocate($barcode,255,255,255);

            imagefill($barcode,0,0, $black);
            imagefilledrectangle($barcode, $this->border, $this->border,$this->width - $this->border -1,$this->barheight - $this->border -1, $white);
            $bar_position = $this->padding;

            for($count = 0; $count < 15; $count++) {
                $character = $code[$count];

                for($subcount = 0; $subcount < strlen($this->left_side_code[$character]); $subcount++) {
                    if($count < 7) {
                        $color = ($this->left_side_code[$character][$subcount] == 0) ? $white : $black;
                    }
                    else {
                        $color = ($this->right_side_code[$character][$subcount] == 0) ? $white : $black;
                    }

                    if(strlen($this->left_side_code[$character]) == 7) {
                        $height_offset = $this->height * 0.05;
                    } else {
                        $height_offset = 0;
                    }

                    imagefilledrectangle($barcode, $bar_position, $this->padding, $bar_position + $this->barwidth -1,$this->barheight - $height_offset - $this->padding, $color);

                    //move position for the next line
                    $bar_position += $this->barwidth;

                }
            }

            $font = 'Aller_Rg.ttf';

            //the numbers to display
            $first_digit            = substr($upc_code,0,1);
            $left_manufacturer_code = substr($upc_code,1,5);
            $right_product_code     = substr($upc_code,6,5);
            $last_digit             = substr($upc_code,11,1);

            //calculate the variables for the imagettftext
            $font_size = 8 * $this->barwidth;
            $x1        = 2 * $this->barwidth;
            $x2        = 18 * $this->barwidth;
            $x3        = 64 * $this->barwidth;
            $x4        = 106 * $this->barwidth;
            $y         = 66 * $this->barwidth;


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
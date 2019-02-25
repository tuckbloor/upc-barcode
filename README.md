UPC Barcode Generator

A Simple Class that you pass the 12 digits for A UPC  code and the class displays the generated barcode
this can be saved instead

The following are PHP requirements

PHP >= 5.3
GD Extension

For Testing

    <?php
    
        require_once __DIR__ . '/vendor/autoload.php';
    
        $code = "033984026216";
    
        $upc = new App\Upc(2, $code);
        $upc->generate();
    ?>
    

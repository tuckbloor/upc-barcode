<?php

    require_once __DIR__ . '/vendor/autoload.php';

    $code = "033984026216";

    $upc = new App\Upc(2, $code);
    $upc->generate();
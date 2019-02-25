<?php

    include('Class/Upc.php');

    $code = "033984026216";

    $upc = new App\Upc(2, $code);
    $upc->generate();
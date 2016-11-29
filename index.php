<?php

    include('Class/Upc.php');
    $upc = new Upc(4);
    $code = '033984026216';
    $upc->generate($code);
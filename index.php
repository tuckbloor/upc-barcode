<?php
include('Class/Upc.php');
$upc = new Upc(4);
$number = '033984026216';
$upc->build($number);
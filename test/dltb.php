<?php
include_once("../database/dbpersonhours.php");

$t = calcPersonHours('vmsroot');
echo($t);
echo('\n');
$t = getEvetnPartipants(122);
foreach ($t as $p)
    {
        print($p);
        print('|');
    }


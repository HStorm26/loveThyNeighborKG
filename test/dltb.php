<?php
include_once("../database/dbpersonhours.php");
include_once("../database/dbRoles.php");
include_once("../database/dbRoleEvents.php");


$r = calcTop10();
var_dump($r);

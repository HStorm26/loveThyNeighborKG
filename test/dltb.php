<?php
include_once("../database/dbpersonhours.php");
include_once("../database/dbRoles.php");

$r = getRolesForPersonEvent("email", 248);
var_dump($r);
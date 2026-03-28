<?php
include_once("../database/dbpersonhours.php");
include_once("../database/dbRoles.php");
include_once("../database/dbRoleEvents.php");

$r = getRolesForPersonEvent("email", 248);
var_dump($r);
addRoleToEvent(248,1,3);
updateNotesForRoleEvent("be there or be square",1,248);
$r = getNotesForRoleEvent(1,248);
var_dump($r);
removeRoleEvent(1,248);

$r = getRolesForPersonEvent("email",248);
var_dump($r);
$r = getRolesForPerson("email");
var_dump($r);

$r = getPersonsForRoleEvent(1,248);
var_dump($r);

$r = getPersonsForRole(1);
var_dump($r);

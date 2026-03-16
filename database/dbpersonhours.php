<?php
/*
 * Author: Daniel Leaman
 * purpose: to manage the functions necessary to interact with the dbpersonhours table in the 
 *  database for LTN
 * 
*/

include_once('dbinfo.php');


/* this will retun nothing.
 * this should add a person to a scheduled event
 */ 
function registerPersonForEvent($eventid,$personid,$roleid)
{
    $con = connect();
    $querey = "INSERT IGNORE INTO `dbpersonhours` (`personID`, `eventID`, `roleID`, `start_time`, `end_time`) VALUES (?, ?, ?, NULL, NULL)";
    $stmt = $con->prepare($querey);
    if ($stmt)
        {
            $stmt->bind_param("sii", $personid,$eventid,$roleid);
            $stmt->execute();


            }

    
}

//update start time

//update end time

//calc person hours

//calc event hours

//get person event hours

//get event partisipants


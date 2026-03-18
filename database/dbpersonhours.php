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

    mysqli_close($con);
}

//update start time
//this one sets it to now
function updateStartTime($eventid,$personid,$roleid)
{
    $con = connect();
    $querey = 'UPDATE `dbpersonhours` SET `start_time` = NOW() where `eventID` = ? AND `personID` = ? AND `roleID` = ?';
    $stmt = $con->prepare($querey);
    if ($stmt)
        {
            $stmt->bind_param("isi", $eventid,$personid,$roleid);
            $stmt->execute();
        }
        mysqli_close($con);
}
//update end time
function updateEndTime($eventid,$personid,$roleid)
{
    $con = connect();
    $querey = 'UPDATE `dbpersonhours` SET `end_time` = NOW() where `eventID` = ? AND `personID` = ? AND `roleID` = ?';
    $stmt = $con->prepare($querey);
    if ($stmt)
        {
            $stmt->bind_param("isi", $eventid,$personid,$roleid);
            $stmt->execute();
        }
        mysqli_close($con);
}
//calc person hours
function calcPersonHours($personid)
{
    $con = connect();
    $querey = "SELECT sum(TIMESTAMPDIFF(MINUTE, `start_time`, `end_time`)) FROM `dbpersonhours` WHERE `personID` = ?";
    $stmt = $con->prepare($querey);
    if ($stmt)
        {
            $stmt->bind_param("s", $personid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $hours = $result->fetch_row();
            
            
            return (int)$hours[0];
        }
        mysqli_close($con);
    
}

//calc event hours

//get person event hours



/*
 $persons = array();
            while ($row = $result->fetch_assoc()) {
                $persons[] = $row;
            }
            mysqli_close($con);
            return $persons;
        
        mysqli_close($con);
    return [];
    */

//get event partisipants

function getEvetnPartipants($eventid){
$con = connect();
    $querey = "SELECT DISTINCT`personID` FROM `dbpersonhours` WHERE `eventID` = ?";
    $stmt = $con->prepare($querey);
    if ($stmt)
        {
            $stmt->bind_param("i", $eventid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $persons = array();
            $persons = $result->fetch_all();
            for ($i = 0; $i < sizeof($persons); $i++)
                {
                    $persons[$i] = $persons[$i][0];
                }
        
            return $persons;
        }
        mysqli_close($con);
    
}
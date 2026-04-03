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

//unregister for event I want it to un reg to no need role
function unregisterPersonForEvent($eventid,$personid)
{
    $con = connect();
    $querey = "DELETE FROM `dbpersonhours` WHERE `dbpersonhours`.`personID` = ? AND `dbpersonhours`.`eventID` = ?";
    $stmt = $con->prepare($querey);
    if ($stmt)
        {
            $stmt->bind_param("si", $personid,$eventid,);
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

function calcTop10()
{
     $con = connect();
    $querey = "SELECT p.first_name,p.last_name, sum(TIMESTAMPDIFF(MINUTE, h.start_time, h.end_time)) as `m` FROM `dbpersonhours` as `h` left join `dbpersons` as `p` on h.personID = p.id GROUP BY p.id, p.first_name, p.last_name order by m desc limit 10";
    $stmt = $con->prepare($querey);
    $stmt->execute();
    $stmt->bind_result($f,$l,$tm);
    $rows = [];
    while ($stmt->fetch())
        {
            $h = intdiv((int)$tm, 60);
            $m = (int)$tm % 60;
            $rows[] = [$f,$l,$h,$m];
        }
    $con->close();
    return $rows;
}

function roleHoursForDateRange($sd,$ed)
{
    $con = connect();
    $querey = "SELECT r.role, sum(TIMESTAMPDIFF(MINUTE, h.start_time, h.end_time)) as `m` FROM `dbpersonhours` as `h` left join `dbroles` as `r` on h.roleID = r.role_id WHERE h.start_time >= ? AND h.start_time <  ? GROUP BY r.role order by m desc";
    $stmt = $con->prepare($querey);
    $stmt->bind_param('ss',$sd,$ed);
    $stmt->execute();
    $stmt->bind_result($r,$tm);
    $rows = [];
    while ($stmt->fetch())
        {
            $h = intdiv((int)$tm,60);
            $m = ((int)$tm % 60);
            $rows[] = [$r,$h,$m];
        }
    $con->close();
    return $rows;
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
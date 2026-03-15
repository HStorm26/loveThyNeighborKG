<?php

// dbroles DOESN'T EXIST YET, update as needed after - DT
include_once('dbinfo.php');
include_once(dirname(__FILE__).'/../dbEvents.php');


function addRoleToEvent($eventID, $roleID, $capacity) {
    $con = connect();

    $query = "INSERT INTO dbroleevents (eventID, roleID, capacity) VALUES ('$eventID', '$roleID', '$capacity'";

    $result = mysqli_query($con, $query);

    if (!$result) {
        mysqli_close($con);
        return null;
    }

    mysqli_commit($con);
    mysqli_close($con);
    return true;
}



function getRolesForEvent($eventID) {
    $con = connect();

    $query = "SELECT re.eventID, re.roleID, re.capacity, r.role_description
                FROM dbroleevents re 
                JOIN roles r 
                ON re.roleID = r.roleID 
                WHERE re.eventID = '$eventID'";

    $result = mysqli_query($con, $query);

    $theRoleEvents = array();

    while ($resultRow = mysqli_fetch_assoc($result)) {
        $roleEvent = array(
            "eventID" => $resultRow['eventID'],
            "roleID" => $resultRow['roleID'],
            "capacity" => $resultRow['capacity'],
            "role_description" => $resultRow['role_description']
        );
    }

    mysqli_close($con);

    return $theRoleEvents;
}

function getRoleEventCapacity($eventID, $roleID) {
    $con = connect();

    $query = "SELECT capacity FROM dbroleevents WHERE eventID = '$eventID' AND roleID = '$roleID'";

    $result = mysqli_query($con, $query);

    $row = mysqli_fetch_assoc($result);

    mysqli_close($con);

    if (!$row) {
        return null;
    }

    return $row['capacity'];
}

function updateRoleEventCapacity($eventID, $roleID, $capacity) {
    $con = connect();

    $query = "UPDATE dbroleevents SET capacity = '$capacity' WHERE eventID = '$eventID' AND roleID = '$roleID'";

    $result = mysqli_query($con, $query);

    if (!$result) {
        mysqli_close($con);
        return null;
    }

    mysqli_commit($con);
    mysqli_close($con);

    return true;
}



?>
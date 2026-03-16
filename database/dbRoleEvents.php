<?php

include_once('dbinfo.php');

/**
*   Role-Events functions
*/

// for now, adds given eventID & roleID with capacity to this db for future use
function addRoleToEvent($eventID, $roleID, $capacity) {
    $con = connect();

    $query = "INSERT INTO dbroleevents (eventID, roleID, capacity) VALUES ('$eventID', '$roleID', '$capacity')";

    $result = mysqli_query($con, $query);

    if (!$result) {
        mysqli_close($con);
        return null;
    }

    mysqli_commit($con);
    mysqli_close($con);
    return true;
}


// ===== might need to change to accommodate dbRoles better? =====
//
// for now, grabs the eventID, roleID, cap, & role description
function getRolesForEvent($eventID) {
    $con = connect();
    // role_id is coming from dbroles.sql
    $query = "SELECT re.eventID, re.roleID, re.capacity, r.role, r.role_description
                FROM dbroleevents re 
                JOIN dbroles r 
                ON re.roleID = r.role_id     
                WHERE re.eventID = '$eventID'";

    $result = mysqli_query($con, $query);

    $theRoleEvents = array();

    while ($resultRow = mysqli_fetch_assoc($result)) {
        $roleEvent = array(
            "eventID" => $resultRow['eventID'],
            "roleID" => $resultRow['roleID'],
            "capacity" => $resultRow['capacity'],
            "role_name" => $resultRow['role'],
            "role_description" => $resultRow['role_description']
            //"currentSignups" => $resultRow['currentSignups']
        );

        $theRoleEvents[] = $roleEvent;
    }

    mysqli_close($con);

    return $theRoleEvents;
}


// grabs capacity from given role-event
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

// updates the capacity from a given role-event
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

// adds the total role capacity[s] for an event
function getEventCapacity($eventID) {
    $con = connect();

    $query = "SELECT SUM(capacity) AS totalCap FROM dbroleevents WHERE eventID = '$eventID'";
    
    $result = mysqli_query($con, $query);

    $row = mysqli_fetch_assoc($result);

    mysqli_close($con);

    if (!$row || $row['totalCap'] === null) {
        return 0;
    }

    return $row['totalCap'];
}

// Save the selected roles
function save_event_roles($eventID, $roles) {
    $connection = connect();

    foreach ($roles as $roleID => $count) {
        $roleID = (int)$roleID;
        $count = (int)$count;

        if ($count > 0) {
            $query = "
                INSERT INTO dbroleevents (eventID, roleID, capacity)
                VALUES ($eventID, $roleID, $count)
            ";
            mysqli_query($connection, $query);
        }
    }

    mysqli_close($connection);
}
?>
<?php
include_once('dbinfo.php');

/**
 * Role management functions.
 */

function add_role($role_name, $role_description) {
    $con = connect();
    $stmt = $con->prepare("INSERT INTO dbroles (`role`, `role_description`) VALUES (?, ?)");
    if (!$stmt) {
        mysqli_close($con);
        return false;
    }
    $stmt->bind_param("ss", $role_name, $role_description);
    $ok = $stmt->execute();
    $id = $con->insert_id;
    $stmt->close();
    mysqli_close($con);
    return $ok ? $id : false;
}

function update_role_description($role_id, $role_description) {
    $con = connect();
    $stmt = $con->prepare("UPDATE dbroles SET role_description = ? WHERE role_id = ?");
    if (!$stmt) {
        mysqli_close($con);
        return false;
    }
    $stmt->bind_param("si", $role_description, $role_id);
    $ok = $stmt->execute();
    $stmt->close();
    mysqli_close($con);
    return $ok;
}

function delete_role($role_id) {
    $con = connect();

    $stmt = $con->prepare("DELETE FROM person_roles WHERE role_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $con->prepare("DELETE FROM dbroles WHERE role_id = ?");
    if (!$stmt) {
        mysqli_close($con);
        return false;
    }
    $stmt->bind_param("i", $role_id);
    $ok = $stmt->execute();
    $stmt->close();
    mysqli_close($con);
    return $ok;
}

function get_roles() {
    $con = connect();
    $result = mysqli_query($con, "SELECT `role_id`, `role`, `role_description` FROM `dbroles` ORDER BY `role`");
    if (!$result) {
        mysqli_close($con);
        return [];
    }
    $roles = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $roles[] = $row;
    }
    mysqli_close($con);
    return $roles;
}

function get_role_description($role_id) {
    $con = connect();
    $stmt = $con->prepare("SELECT role_description FROM dbroles WHERE role_id = ?");
    if (!$stmt) {
        mysqli_close($con);
        return null;
    }
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $stmt->bind_result($desc);
    $stmt->fetch();
    $stmt->close();
    mysqli_close($con);
    return $desc;
}
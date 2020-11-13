<?php
include "includes/db_connect.php";

$actionNoMatch = true;
$response = array();
$response["success"] = false;

@$action = $_GET["action"];
$login_id = filter_var($_GET["login_id"], FILTER_SANITIZE_NUMBER_INT);
$ticket = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET["ticket"]);


if ($_SERVER['REQUEST_METHOD'] != "GET") {
    header('HTTP/1.0 405 Method Not Allowed');
    exit;
}


// Check in DB if login_id and ticket matches
$sql = "SELECT role, ticket FROM users WHERE id=? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $login_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // output data of each row
    while ($row = $result->fetch_assoc()) {
        if ($row["ticket"] === $ticket) {
            $role = $row["role"];
        }
    }
} else {
    header('HTTP/1.0 403 Forbidden');
    $response["login_id"] = $login_id;
    $response["ticket"] = $ticket;
    echo json_encode($response);
    exit();
}



if (!isset($_GET["action"])) {
    $response["msg"] = "No action defined";
    echo json_encode($response);
} else {
    if ($action == "loadTopics") {
        $actionNoMatch = false;

        $sql = "SELECT * FROM topics";
        $result = $mysqli->query($sql);

        $topics = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $topics[] = $row;
            }
            $response["topics"] = $topics;
            $response["success"] = true;
        } else {
            $response["error"] = "Returned 0 rows";
        }

        echo json_encode($response);
    }


    else if ($action == "loadClasses") {
        $actionNoMatch = false;

        $sql = "SELECT c.id, c.name FROM classes c INNER JOIN classes_relations cr on c.id = cr.class_id and cr.user_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $login_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $classes = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $classes[] = $row;
            }
            $response["classes"] = $classes;
            $response["success"] = true;
        } else {
            $response["error"] = "Returned 0 rows";
        }

        echo json_encode($response);
    }



    // Define actions that only teachers and admins can perform
    if ($role == "teacher" || $role == "admin") {

    }


    // Define actions that only admins can perform
    if ($role == "admin") {

    }



    if ($actionNoMatch) {
        $response["msg"] = "No action matched";
        echo json_encode($response);
    }
}
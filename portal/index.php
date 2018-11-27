<?php 
    session_start();
    include '../includes/db_connect.php';
    $gw_id = $_GET['gw_id'];
    $gw_sn = $_GET['gw_sn'];
    $mac = $_GET['mac'];

    //Check whether the gateway is valid or not 
    $stmt = $conn->prepare("SELECT * FROM gateways WHERE gw_sn = ?;");
    $stmt->bind_param('s', $gw_sn); 
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows <= 0) {
        die("Gateway is invalid!");
    } 
    //Check whether this MAC has session or not
    $stmt = $conn->prepare("SELECT * FROM sessions WHERE mac = ?;");
    $stmt->bind_param('s', $mac); 
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) { // If it has a session
        //get parameters from this session
        $row = $result->fetch_assoc();
        $redirect_url = $row['url'];
        $conn->close();
        header('Location: ' . $redirect_url);
    }
?>
<?php
    $gw_sn = $_GET['gw_sn'];
    $stage = $_GET['stage'];
    $mac = $_GET['mac'];

    include '../includes/db_connect.php';
    include '../includes/agent.php';
    
    //Check whether the gateway is valid or not
    $stmt = $conn->prepare("SELECT * FROM gateways RIGHT JOIN gateway_groups ON gateways.gwgroup_id = gateway_groups.gwgroup_id WHERE gw_sn = ?;");
    $stmt->bind_param('s', $gw_sn);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows <= 0) {
        die("Gateway is invalid!");
    }
    
    $row = $result->fetch_assoc();
    $page_id = $row['page_id'];
    $gw_name = $row['gw_name'];
    $gwgroup_name = $row['gwgroup_name'];
    
    $stmt = $conn->prepare("SELECT * FROM pages WHERE page_id = ?;");
    $stmt->bind_param('s', $page_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows <= 0) {
        die("There is no page associated to this gateway");
    }

    //if there is a gateway associated to this gateway
    //get the connection type
    $row = $result->fetch_assoc();   
    $connection_type = $row['connection_type'];
    
    switch($stage) {
        case 'login' :
            $token = $_GET['token'];
            //Check whether this MAC/Token pair is valid or not
            $stmt = $conn->prepare("SELECT * FROM sessions RIGHT JOIN profiles ON sessions.profile_id = profiles.profile_id WHERE sessions.mac = ? AND sessions.token = ?");
            $stmt->bind_param("ss", $mac, $token);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $capacity = $row['capacity'];

                $incoming = $_GET['incoming'];
                $outgoing = $_GET['outgoing'];


                //Update sessions
                $stmt = $conn->prepare("UPDATE sessions SET incoming = incoming + ?, outgoing = outgoing + ? WHERE mac = ?");
                $stmt->bind_param("iis", $incoming, $outgoing, $mac);
                $stmt->execute();
                $auth_response = "Auth: 1";            
            } else {
                 $auth_response = "Auth: 0";
            }
            break;
        case 'query' :           
            //Check whether this MAC has session or not
            $stmt = $conn->prepare("SELECT * FROM sessions INNER JOIN profiles ON sessions.profile_id = profiles.profile_id WHERE sessions.mac = ? AND sessions.session_id IS NOT NULL;");
            $stmt->bind_param('s', $mac);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {                
                $row = $result->fetch_assoc();
                $token = $row['token'];

                $session_timeout = 0;
                $out_of_capacity = 0;

                //Check timeout
                $timeout = $row['timeout'];
                $time = strtotime($row['time']);
                $now = strtotime(date("Y-m-d H:i:s"));
                $diff = ($now - $time) / 60;
                if (($diff <= $timeout) || ($timeout == 0)) { //If this sessions NOT timeout
                    $session_timeout = 0;            
                }  else { 
                    $session_timeout = 1;
                }

                //Check capacity
                $incoming = $row['incoming'];
                $outgoing = $row['outgoing'];

                $capacity = $row['capacity'];

                if (($incoming > $capacity) || ($outgoing > $capacity)) {
                    $out_of_capacity = 1;
                } else {
                    $out_of_capacity = 0;
                }

                if ($capacity == 0) 
                    $out_of_capacity = 0;

                if (($session_timeout == 1) || ($out_of_capacity == 1)) 
                    $auth_response = "Auth: 0";
                else {
                    //Update history
                    $stmt = $conn->prepare("SELECT * FROM history WHERE mac  = ? ORDER BY time DESC");
                    $stmt->bind_param("s", $mac);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $browser = $row['browser'];
                    $os = $row['os'];
                    $device = $row['device'];
                    $page_id = $row['page_id'];
                    $stmt = $conn->prepare("INSERT INTO history (mac, browser, os, device, connection_type, gw_name, gwgroup_name, page_id) VALUES(?,?,?,?,?,?,?,?)");
                    $stmt->bind_param("sssssssi", $mac, $browser, $os, $device, $connection_type, $gw_name, $gwgroup_name, $page_id);
                    $stmt->execute();
                    $auth_response = "Auth: 1";
                }
            } else {
                $auth_response = "Auth: 0";
            }
            break;
        case 'counters' : 
            $token = $_GET['token'];

            //Check whether this mac has session or not
            $stmt = $conn->prepare("SELECT * FROM sessions RIGHT JOIN profiles ON sessions.profile_id = profiles.profile_id WHERE sessions.mac = ? AND sessions.session_id IS NOT NULL;");
            $stmt->bind_param("s", $mac);
            $stmt->execute();
            $result = $stmt->get_result();

            //If the session is valid
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $capacity = $row['capacity'];

                $incoming = $row['incoming'];
                $outgoing = $row['outgoing'];
                $ok = 1;

                $incoming .= round($_GET['incoming'] / (1024*1024));
                $outgoing .= round($_GET['outgoing'] / (1024*1024));

                $stmt = $conn->prepare("UPDATE sessions SET incoming = ?, outgoing =  ? WHERE mac = ?");
                $stmt->bind_param("iis", $incoming, $outgoing, $mac);
                $stmt->execute();

                if (($capacity < $incoming) || ($capacity < $outgoing)) 
                    $ok = 0;
                if ($capacity == 0) {
                    $ok = 1;
                }
                if ($ok == 1) {
                    $auth_response = "Auth: 1";
                } else { //if session is out of its capacity
                    $auth_response = "Auth: 0";
                } 
            } else { //if session is invalid
                $auth_response = "Auth: 0";
            }     
            break;
        case 'logout' :
            $auth_response = "Auth: 0";
            break;
    }
    $conn->close();
    echo $auth_response;    
?>

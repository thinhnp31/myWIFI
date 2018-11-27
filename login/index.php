<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
    session_start();
    include '../includes/db_connect.php'; 
    include '../includes/agent.php'; 

    //GET all parameters
    if (isset($_GET['gw_id'])) {
        $gw_id = $_GET['gw_id'];
    }
    if (isset($_GET['gw_sn'])) {
        $gw_sn = $_GET['gw_sn'];
    }
    if (isset($_GET['gw_address'])) {
        $gw_address = $_GET['gw_address'];
    }
    if (isset($_GET['gw_port'])) {
        $gw_port = $_GET['gw_port'];
    }
    if (isset($_GET['ip'])) {
        $ip = $_GET['ip'];
    }
    if (isset($_GET['mac'])) {
        $mac = $_GET['mac'];
    }
    if (isset($_GET['apmac'])) {
        $apmac = $_GET['apmac'];
    }
    if (isset($_GET['ssid'])) {
        $ssid = $_GET['ssid'];
    }
    if (isset($_GET['url'])) {
        $url = $_GET['url'];
    }
    
    //Check whether the gateway is valid or not 
    $stmt = $conn->prepare("SELECT * FROM gateways RIGHT JOIN gateway_groups ON gateways.gwgroup_id = gateway_groups.gwgroup_id WHERE gw_sn = ? ");
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

    //Check whether there is a page associated to this gateway or not 
    $stmt = $conn->prepare("SELECT * FROM pages WHERE page_id = ?;");
    $stmt->bind_param('s', $page_id); 
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows <= 0) {
        die("There is no page associated to this gateway");
    }
    //If this gateway is associated to a page
    //get parameters from the page
    $row = $result->fetch_assoc();
    $connection_type = $row['connection_type'];

    //Update history
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $browser = getBrowser($user_agent);
    $os = getOS($user_agent);
    $device = getDevice($mac);
    $stmt = $conn->prepare("INSERT INTO history (mac,  browser, os, device, connection_type, gw_name, gwgroup_name, page_id) VALUES(?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssssi", $mac, $browser, $os, $device, $connection_type, $gw_name, $gwgroup_name, $page_id);
    $stmt->execute();

    //Check whether this MAC has session or not
    $stmt = $conn->prepare("SELECT * FROM sessions RIGHT JOIN profiles ON sessions.profile_id = profiles.profile_id WHERE sessions.mac = ? ");
    $stmt->bind_param('s', $mac); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) { // If it has a session
        //get parameters from this session
        $row = $result->fetch_assoc();
        $token = $row['token'];
        $auto_renew = $row['auto_renew'];
        
        $session_timeout = 0;
        $out_of_capacity = 0;

        //Check timeout
        $timeout = $row['timeout'];
        $time = strtotime($row['time']);
        $now = strtotime(date("Y-m-d H:i:s"));
        $diff = round(($now - $time) / 60);
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

        if (($session_timeout == 0) && ($out_of_capacity == 0)) { //if session NOT timeout and NOT out of capacity
            //auto login
            $redirect_url = "http://".$gw_address.":".$gw_port."/auth/?token=".$token;
        } else {
            if ($auto_renew == 1) { //if auto renew is enabled
                //delete current session
                $stmt = $conn->prepare("DELETE FROM sessions WHERE mac = ?");
                $stmt->bind_param("s", $mac);
                $stmt->execute();
                //Then display the login page
                switch ($connection_type) {
                    case 'image':
                        $redirect_url = "image.php?page_id=" . $page_id . "&gw_sn=" . $gw_sn . "&gw_address=" . $gw_address . "&gw_port=" . $gw_port . "&url=" . $url . "&mac=" . $mac;
                        break;
                    case 'local_account':
                        $redirect_url = "local_account.php?page_id=" . $page_id . "&gw_sn=" . $gw_sn . "&gw_address=" . $gw_address . "&gw_port=" . $gw_port . "&url=" . $url . "&mac=" . $mac;
                        break;
                    default:
                        # code...
                        break;
                }
            } else { //if auto renew is NOT enable
                //Display warning page
                $redirect_url = "warning.php?mac=" . $mac;
            }
        }                        
    } else {
        //If there is no session
        //Display the login page
        switch ($connection_type) {
            case 'image':
                $redirect_url = "image.php?page_id=" . $page_id . "&gw_sn=" . $gw_sn . "&gw_address=" . $gw_address . "&gw_port=" . $gw_port . "&url=" . $url . "&mac=" . $mac;
                break;
            case 'local_account':
                $redirect_url = "local_account.php?page_id=" . $page_id . "&gw_sn=" . $gw_sn . "&gw_address=" . $gw_address . "&gw_port=" . $gw_port . "&url=" . $url . "&mac=" . $mac;
                break;
            default:
                # code...
                break;
        }
    }
    header('Location: ' . $redirect_url);
?>
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
    if (isset($_GET['page_id'])) {
        $page_id = $_GET['page_id'];
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
    if (isset($_GET['url'])) {
        $url = $_GET['url'];
    }
    if (isset($_GET['mac'])) {
        $mac = $_GET['mac'];
    }
    
    //Check whether the gateway is valid or not 
    $stmt = $conn->prepare("SELECT * FROM gateways RIGHT JOIN gateway_groups ON gateways.gwgroup_id = gateway_groups.gwgroup_id WHERE gw_sn = ?  ");
    $stmt->bind_param('s', $gw_sn); 
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows <= 0) {
        die("Gateway is invalid!");
    } 
    $row = $result->fetch_assoc();
    $id = $row['id'];
    $gwgroup_id = $row['gwgroup_id'];
    $gwgroup_name = $row['gwgroup_name'];
    
    //Get parameters from the page associated to this gateway
    $stmt = $conn->prepare("SELECT * FROM pages WHERE page_id = ? ");
    $stmt->bind_param('i', $page_id); 
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $background = $row['background'];
    $logo = $row['logo'];
    $connection_type = $row['connection_type']; 
    $profile_id = $row['profile_id'];
    $welcome = $row['welcome'];
    
    if (isset($_GET['submit'])) { //IF user click submit        
      //Create a token
      $token = md5(uniqid());

      //add this device to session table      
      if ($_GET['submit'] == "Tiếp tục") {
        $interested = 0;
      } else {
        $interested = 1;
        $url = $ad_url;
        $stmt2 = $conn->prepare("UPDATE pages SET interested = interested + 1 WHERE page_id = ?");
        $stmt2->bind_param("i", $page_id);
        $stmt2->execute();
      }

      $stmt = $conn->prepare("UPDATE pages SET displayed = displayed + 1 WHERE page_id = ?");
      $stmt->bind_param("i", $page_id);
      $stmt->execute();

      $stmt = $conn->prepare("INSERT INTO sessions(mac, token, url, page_id, id, gwgroup_id, profile_id, gw_sn) VALUES (?,?,?,?,?,?,?,?)");
      $stmt->bind_param('sssiiiis', $mac, $token, $url, $page_id, $id, $gwgroup_id, $profile_id, $gw_sn);

      $stmt->execute();

      $redirect_url = "http://".$gw_address.":".$gw_port."/auth/?token=".$token;     
      header('Location: ' . $redirect_url);
      $conn->close();    
    } 
?>

<html lang="en">

<head>
  <!-- Title -->
  <title>MyWifi</title>

  <!-- Required Meta Tags Always Come First -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/jquery-3.3.1.slim.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/popper.min.js"></script>  
</head>

<body >
  <div class="text-center"><img src="../images/logo/<?php echo $logo; ?>" width=20%; class="mt-4"></div>
  <div class="text-center mt-4 mb-4"><h1><?php echo $welcome;?></h1></div>
  <img src="../images/background/<?php echo $background; ?>" width=100%;>
    <div class="form-group align-middle text-center pt-3">
      <form method="GET" action="#">
        <input type="hidden" name="page_id" value=<?php echo $page_id;?> >
        <input type="hidden" name="gw_sn" value="<?php echo $gw_sn;?>" >
        <input type="hidden" name="gw_address" value="<?php echo $gw_address;?>" >
        <input type="hidden" name="gw_port" value="<?php echo $gw_port;?>" >
        <input type="hidden" name="mac" value="<?php echo $mac;?>" >
        <input type="hidden" name="url" value="<?php echo $url;?>" >
       
          
            <input type="submit" class="btn btn-success mr-4" name="submit" value="Tiếp tục">
        
       
      </form>
  </div>
</body>

</html>

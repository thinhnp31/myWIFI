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
    $ad_url = $row['ad_url'];
    if (isset($row['youtube_link'])) {
      $youtube_link = explode("v=", $row['youtube_link'])[1];
      $youtube_link = "https://www.youtube.com/embed/" . $youtube_link  . "?autoplay=1&controls=0";
    }
    
    $countdown = $row['countdown'];
    if ($ad_url != "")
      $url = $ad_url;
    
    if (isset($_GET['submit'])) { //IF user click submit        
      //Create a token
      $token = md5(uniqid());

      $stmt = $conn->prepare("UPDATE pages SET displayed = displayed + 1 WHERE page_id = ?");
      $stmt->bind_param("i", $page_id);
      $stmt->execute();

      $stmt = $conn->prepare("INSERT INTO sessions(mac, token, url, page_id, gwgroup_id, profile_id, gw_sn) VALUES (?,?,?,?,?,?,?)");
      $stmt->bind_param('sssiiis', $mac, $token, $url, $page_id, $gwgroup_id, $profile_id,   $gw_sn);

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

<style type="text/css">
iframe {
  height: 56.25vw;
  left: 50%;
  min-width: 100%;
  min-height: 100%;
  top: 50%;
  width: 177.77777777778vh;
}

</style>

<body >
  <div class="text-center">
    <img src="../images/logo/<?php echo $logo; ?>" width=20%; class="mt-4"></div>
  <div class="text-center mt-4 mb-4">
    <h1><?php echo $welcome;?></h1></div>
  <div class="row">
    <iframe src="<?php echo $youtube_link;?>" allow="autoplay" width=100%></iframe>
  </div>

  
  <div class="form-group align-middle text-center pt-3">
    <form method="GET" action="#">
      <input type="hidden" name="page_id" value=<?php echo $page_id;?> >
      <input type="hidden" name="gw_sn" value="<?php echo $gw_sn;?>" >
      <input type="hidden" name="gw_address" value="<?php echo $gw_address;?>" >
      <input type="hidden" name="gw_port" value="<?php echo $gw_port;?>" >
      <input type="hidden" name="mac" value="<?php echo $mac;?>" >
      <input type="hidden" name="url" value="<?php echo $url;?>" >
       
          
      <input type="submit" class="btn btn-success mr-4" name="submit" id="submit_button" value="Tiếp tục">
        
       
    </form>
  </div>

  <script type="text/javascript">
    var countdown = <?php echo $countdown;?>;

    var x  = setInterval(function() {
      countdown -= 1;
      $("#submit_button").val("Tiếp tục (" + countdown + ")");
      $("#submit_button").attr("disabled", true);

      if (countdown <= 0) {
        clearInterval(x);
        $("#submit_button").val("Tiếp tục");
        $("#submit_button").attr("disabled", false);
      }
        
    }, 1000);
  </script>
</body>

</html>

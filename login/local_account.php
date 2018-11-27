<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
    session_start();
    include '../includes/db_connect.php'; 
    require 'vendor/autoload.php';
    use Sinergi\BrowserDetector\Browser; 
    use Sinergi\BrowserDetector\Os; 
    use Sinergi\BrowserDetector\Device;

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
    $stmt = $conn->prepare("SELECT * FROM gateways WHERE gw_sn = ? ");
    $stmt->bind_param('s', $gw_sn); 
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows <= 0) {
        die("Gateway is invalid!");
    } 
    $row = $result->fetch_assoc();
    $id = $row['id'];
    $gwgroup_id = $row['gwgroup_id'];
    
    $stmt = $conn->prepare("SELECT * FROM pages WHERE page_id = ? ");
    $stmt->bind_param('i', $page_id); 
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $background = $row['background'];
    $logo = $row['logo'];
    $connection_type = $row['connection_type'];
    $welcome = $row['welcome'];
    $profile_id = $row['profile_id'];
    
    if (isset($_GET['submit'])) { //IF user click submit        
        //Get username and password
        $username = $_GET['username'];
        $password = $_GET['password'];
        $password = md5($password);
        //Check whether username/password is valid or not        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND PASSWORD = ?");
        $stmt->bind_param('ss', $username, $password); 
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) { //if username/password is valid

            //Check device limit & expiry
            $row = $result->fetch_assoc();          
            $device_limit = $row['device_limit'];
            $user_status = $row['user_status'];
            $now = strtotime(date("Y-m-d"));

            $stmt = $conn->prepare("SELECT * FROM sessions WHERE username = ?;");
            $stmt->bind_param('s', $username); 
            $stmt->execute();
            $result2 = $stmt->get_result();
            if (($result2->num_rows <= $device_limit) || ($device_limit == 0)) { //If it hasnot reach the limit yet

                //Check Expiry                
                if ($user_status == "active") { //if not expired
                    //Create a token
                    $token = md5(uniqid());
                    //add this device to session table
                    $stmt = $conn->prepare("INSERT INTO sessions(username, mac, token, url, page_id, gwgroup_id, profile_id, gw_sn) VALUES (?,?,?,?,?,?,?,?);");
                    $stmt->bind_param('ssssiiii', $username, $mac, $token, $url, $page_id, $gwgroup_id, $profile_id, $gw_sn); 
                    $stmt->execute();

                    $redirect_url = "http://".$gw_address.":".$gw_port."/auth/?token=".$token;
                    
                    $conn->close();

                    header('Location: '.$redirect_url);
                } else {

                    $alert = "Tài khoản đã hết hạn";
                }
                
            } else {
                $alert = "Số lượng thiết bị sử dụng vượt quá hạn mức";
            }            
        } else {
            $alert = "Username hoặc Password không đúng";
        }        
    } 
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>myWifi</title>
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="../css/myCSS.css">
        <script src="../js/jquery-3.3.1.slim.min.js" ></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/popper.min.js"></script>
    </head>
    <body class='bg' style="background-image: url(<?php echo "../images/background/" . $background ?>)">
        <?php 
            if (isset($alert)) {
        ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $alert; ?>
                </div>
        <?php
            }
        ?>
        <div class="container">
            <div class="row">
                <div class="col">              
                </div>
                <div class="col-10 mt-3 p-5" style="background-color: white; border-radius: 5px;">
                    <div style="text-align: center">
                        <img src="<?php echo "../images/logo/" . $logo ?>" class="img-fluid mb-3" alt="Responsive image">
                        <div class="text-center mt-4 mb-4"><h1><?php echo $welcome;?></h1></div>
                        <h1>Vui lòng đăng nhập để sử dụng Wi-Fi</h1>
                        <br>
                        <br>
                    </div>
                    <form method="GET" action="#"> 
                        <input type="hidden" name="gw_sn" value="<?php echo $gw_sn; ?>">
                        <input type="hidden" name="gw_address" value="<?php echo $gw_address; ?>">
                        <input type="hidden" name="gw_port" value="<?php echo $gw_port; ?>">
                        <input type="hidden" name="url" value="<?php echo $url; ?>">
                        <input type="hidden" name="mac" value="<?php echo $mac; ?>">
                        <input type="hidden" name="page_id" value="<?php echo $page_id; ?>">
                        <div class="form-group">
                            <label>Username :</label>
                            <input type="text" class="form-control" placeholder="Nhập username" name="username">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" placeholder="Nhập password" name="password">
                        </div>
                        <div style="text-align: center">
                            <button type="submit" class="btn btn-primary btn-lg" name="submit">Đăng nhập</button>   
                        </div>
                    </form>
                </div>
                <div class="col">
                </div>
            </div>
        </div>           
    </body>
</html>

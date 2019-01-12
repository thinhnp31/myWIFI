<?php
    session_start();
    include '../includes/db_connect.php'; 
    $stmt = $conn->prepare("SELECT * FROM sessions RIGHT JOIN profiles ON sessions.profile_id = profiles.profile_id WHERE sessions.mac = ? ");
    $stmt->bind_param("s", $_GET['mac']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $timeout = $row['timeout'];
    $time = strtotime($row['time']);
    $now = strtotime(date("Y-m-d H:i:s"));
    $diff = round(($now - $time) / 60);
    $diff = $timeout - $diff;
    if ($diff < 0) 
      $diff = 0;

    $used_incoming = $row['used_incoming'];
    $used_outgoing = $row['used_outgoing'];
    $capacity = $row['capacity'];
    if ($used_incoming > $used_outgoing) 
      $remain = $capacity - $used_incoming;
    else       
      $remain = $capacity - $used_outgoing;
    if ($remain < 0) 
      $remain = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Title -->
  <title>myWifi</title>

  <!-- Required Meta Tags Always Come First -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/jquery-3.3.1.slim.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/popper.min.js"></script>  
</head>

<style>
.outer {
  display: table;
  position: absolute;
  height: 100%;
  width: 100%;
}

.middle {
  display: table-cell;
  vertical-align: middle;
  
}

.inner {
  margin-left: auto;
  margin-right: auto;
  width: 500px;
  border-radius: 5px;
  border: solid 1px black;
  padding : 30px;
}
</style>

<body>
 
  <div class="outer">
    <div class="middle">
      <div class="inner">
        <h1>Thông báo</h1>
        <p>Vui lòng liên hệ với người quản trị để tiếp tục truy cập Internet</p>
        <div class="row">
          <div class="col-8 align-self-center">
            <label>Thời gian sử dụng : </label>
          </div>

          <div class="col-4 align-self-center text-right">
            <?php if ($timeout == 0) echo "Không giới hạn"; else echo $diff . " phút"; ?>
          </div>
        </div>

        <div class="row justify-content-between mt-4">
          <div class="col-8 align-self-center">
            <label>Dung lượng còn lại : </label>
          </div>
          <div class="col-4 align-self-center text-right">
            <?php if ($capacity == 0) echo "Không giới hạn"; else echo $remain . "/" . $capacity . " MB"; ?> 
          </div>
        </div>
      </div>
    </div>
  </div>



    <!-- Header -->
    
    

    <!-- Login -->
    <!--
    <section class="container g-py-100">
      <div class="row justify-content-center">
        <div class="col-sm-8 col-lg-5">
          <div class="g-brd-around g-brd-gray-light-v4 rounded g-py-40 g-px-30">
            <header class="text-center mb-4">
              <h2 class="h2 g-color-black g-font-weight-600">Thông báo</h2>
            </header>

            <div class="text-center">
              Vui lòng liên hệ với người quản trị để tiếp tục truy cập Internet
            </div>
            

          </div>
        </div>
      </div>
    </section>
     -->

  

</body>

</html>

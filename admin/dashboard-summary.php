<!-- set bodyTheme = "u-card-v1" -->
<?php 
    include "includes/defines.php";  
    include 'includes/db_connect.php'; 

    //Check if this client has been logged in or not
    $logged_in = false;

  
    if (isset($_SESSION['email'])) { //if this client still has a session
        // redirect to MAIN PAGE
        $logged_in = true;
    } else {
        if (isset($_COOKIE['email'])) { //if this client has cookie, 
        //may be I can remember him (or her)
        $token = $_COOKIE['email'] + $_SERVER['REMOTE_ADDR'];
        $token = hash_hmac('md5', $token , salt);
        $stmt = $conn->prepare("SELECT * FROM remember WHERE token = ?");
        $stmt->bind_param('s', $token); 
        $stmt->execute();
        $result = $stmt->get_result();      
            if ($result->num_rows > 0) { //if his token exists in database
                //I  can remember him
                $_SESSION['email'] = $_COOKIE['email'];
                $_SESSION['fullname'] = $_COOKIE['fullname'];
                $logged_in = true;
            } 
        }
    }

    if (!$logged_in) header("Location: " . mywifi_admin . "login.php");
    
    if (isset($_GET['period']))
      $period = $_GET['period'];
    else 
      $period = "day";

    if (isset($_GET['gwgroup_name']))
      $gwgroup_name = $_GET['gwgroup_name'];
    else
      $gwgroup_name = "";

    //Calculate Start Date and End Date for reporting
    switch ($period) {
      case 'day':
        $start_date = date("Y-m-d");
        $end_date = date("Y-m-d", strtotime($start_date . " + 1 days"));
        break;
      case "week":
        $today = date("Y-m-d");
        $day_of_week = date("w", strtotime($today));
        $start_date = date("Y-m-d", strtotime($today . " - ".($day_of_week - 1)."days"));
        $end_date = date("Y-m-d", strtotime($today . " + ".(7 - $day_of_week)."days"));
        $end_date = date("Y-m-d", strtotime($end_date . " + 1 days"));
        break;
      case "month":
        $month = date("m");
        $year = date("Y");
        $days_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $start_date = $year . "-" . $month . "-01";
        $end_date =  $year . "-" . $month . "-" . $days_of_month;
        $end_date = date("Y-m-d", strtotime($end_date . " + 1 days"));
    }

    //Connection Type
    $image = 0;
    $local_account = 0;
    $social = 0;
    $sms = 0;
    $survey = 0;
    $video = 0;
    $voucher = 0;
    if ($gwgroup_name == "") {
      $stmt = $conn->prepare("SELECT connection_type, COUNT(connection_type) AS count FROM history WHERE time BETWEEN DATE(?) AND DATE(?)GROUP BY connection_type ");
      $stmt->bind_param("ss", $start_date, $end_date);
    }
    else {
      $stmt = $conn->prepare("SELECT connection_type, COUNT(connection_type) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ? GROUP BY connection_type ");
      $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      switch ($row['connection_type']) {
        case 'image':
          $image = $row['count'];
          break;
        case 'local_account':
          $local_account = $row['count'];
          break;
        case 'social':
          $social = $row['count'];
          break;
        case 'sms':
          $sms = $row['count'];
          break;
        case 'survey':
          $survey = $row['count'];
          break;
        case 'video':
          $video = $row['count'];
          break;
        case 'voucher':
          $voucher = $row['count'];
          break;
      }
    }
    $image_video = $image + $video;
    $total = $image_video + $local_account + $social + $sms + $survey +$voucher;
    if ($total == 0) $total = 1;
    $percent_image_video = round($image_video / $total * 100);
    $percent_local_account = round($local_account / $total * 100);
    $percent_social = round($social / $total * 100);
    $percent_sms = round($sms / $total * 100);
    $percent_survey = round($survey / $total * 100);
    $percent_voucher = round($voucher / $total * 100);
    $graph_connection_type_data = $image_video . ", ". $local_account . ", " . $social . ", " . $sms. ", " . $survey . ", " . $video . ", " . $voucher;

    //Total Connections
    if ($gwgroup_name == "") {
      $stmt = $conn->prepare("SELECT * FROM history WHERE time BETWEEN DATE(?) AND DATE(?)");
      $stmt->bind_param("ss", $start_date, $end_date);
    }
    else {
      $stmt = $conn->prepare("SELECT * FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?");
      $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
    }    
    $stmt->execute();
    $result = $stmt->get_result();
    $total_connections = $result->num_rows;

    if ($gwgroup_name == "") {
      $stmt = $conn->prepare("SELECT DISTINCT mac FROM history WHERE time BETWEEN DATE(?) AND DATE(?)");
      $stmt->bind_param("ss", $start_date, $end_date);
    }
    else {
      $stmt = $conn->prepare("SELECT DISTINCT mac FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?");
      $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $unique_connections = $result->num_rows;

    //Last Period
    switch ($period) {
      case 'day': //Compare to yesterday
        $last_period = date("Y-m-d", strtotime($start_date . " - 1 days"));
        if ($gwgroup_name == "") {
          $stmt = $conn->prepare("SELECT * FROM history WHERE time BETWEEN DATE(?) AND DATE(?)");
          $stmt->bind_param("ss", $last_period, $start_date);
        }
        else {
          $stmt = $conn->prepare("SELECT * FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?");
          $stmt->bind_param("sss", $last_period, $start_date, $gwgroup_name);
        }    
        $stmt->execute();
        $result = $stmt->get_result();
        $diff_total_connections = $total_connections - $result->num_rows;
        if ($diff_total_connections >= 0) 
          $compare_total_connections = "+ " . $diff_total_connections . " so với ngày hôm qua";
        else
          $compare_total_connections = $diff_total_connections . " so với ngày hôm qua";

        if ($gwgroup_name == "") {
          $stmt = $conn->prepare("SELECT DISTINCT mac FROM history WHERE time BETWEEN DATE(?) AND DATE(?)");
          $stmt->bind_param("ss", $last_period, $start_date);
        }
        else {
          $stmt = $conn->prepare("SELECT DISTINCT mac FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?");
          $stmt->bind_param("sss", $last_period, $start_date, $gwgroup_name);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $diff_unique_connections = $unique_connections - $result->num_rows;

        if ($diff_unique_connections >= 0) 
          $compare_unique_connections = "+ " . $diff_unique_connections . " so với ngày hôm qua";
        else
          $compare_unique_connections = $diff_unique_connections . " so với ngày hôm qua";
        break;
      case "week":
        $last_period = date("Y-m-d", strtotime($start_date . " - 7 days"));
        if ($gwgroup_name == "") {
          $stmt = $conn->prepare("SELECT * FROM history WHERE time BETWEEN DATE(?) AND DATE(?)");
          $stmt->bind_param("ss", $last_period, $start_date);
        }
        else {
          $stmt = $conn->prepare("SELECT * FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?");
          $stmt->bind_param("sss", $last_period, $start_date, $gwgroup_name);
        }    
        $stmt->execute();
        $result = $stmt->get_result();
        $diff_total_connections = $total_connections - $result->num_rows;
        if ($diff_total_connections >= 0) 
          $compare_total_connections = "+ " . $diff_total_connections . " so với tuần trước";
        else
          $compare_total_connections = $diff_total_connections . " so với tuần trước";

        if ($gwgroup_name == "") {
          $stmt = $conn->prepare("SELECT DISTINCT mac FROM history WHERE time BETWEEN DATE(?) AND DATE(?)");
          $stmt->bind_param("ss", $last_period, $start_date);
        }
        else {
          $stmt = $conn->prepare("SELECT DISTINCT mac FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?");
          $stmt->bind_param("sss", $last_period, $start_date, $gwgroup_name);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $diff_unique_connections = $unique_connections - $result->num_rows;

        if ($diff_unique_connections >= 0) 
          $compare_unique_connections = "+ " . $diff_unique_connections . " so với tuần trước";
        else
          $compare_unique_connections = $diff_unique_connections . " so với tuần trước";
        break;
      case "month":
        $last_period = date("Y-m-d", strtotime($start_date . " - 1 months"));
        if ($gwgroup_name == "") {
          $stmt = $conn->prepare("SELECT * FROM history WHERE time BETWEEN DATE(?) AND DATE(?)");
          $stmt->bind_param("ss", $last_period, $start_date);
        }
        else {
          $stmt = $conn->prepare("SELECT * FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?");
          $stmt->bind_param("sss", $last_period, $start_date, $gwgroup_name);
        }    
        $stmt->execute();
        $result = $stmt->get_result();
        $diff_total_connections = $total_connections - $result->num_rows;
        if ($diff_total_connections >= 0) 
          $compare_total_connections = "+ " . $diff_total_connections . " so với tháng trước";
        else
          $compare_total_connections = $diff_total_connections . " so với tháng trước";

        if ($gwgroup_name == "") {
          $stmt = $conn->prepare("SELECT DISTINCT mac FROM history WHERE time BETWEEN DATE(?) AND DATE(?)");
          $stmt->bind_param("ss", $start_date, $end_date);
        }
        else {
          $stmt = $conn->prepare("SELECT DISTINCT mac FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?");
          $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $diff_unique_connections = $unique_connections - $result->num_rows;

        if ($diff_unique_connections >= 0) 
          $compare_unique_connections = "+ " . $diff_unique_connections . " so với tháng trước";
        else
          $compare_unique_connections = $diff_unique_connections . " so với tháng trước";
        break;
    }

    //Peak Day
    if ($gwgroup_name == "") {
       $stmt = $conn->prepare("SELECT DAY(DATE(time)) AS day, COUNT(connection_id) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?))  GROUP BY DAY(DATE(time)) ORDER BY count DESC");
       $stmt->bind_param("ss", $start_date, $end_date);
     } else {
       $stmt = $conn->prepare("SELECT DAY(DATE(time)) AS day, COUNT(connection_id) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ? GROUP BY DAY(DATE(time)) ORDER BY count DESC");
       $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
     }
   
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $peak_day = $row['day'];

    //Peak hour
    if ($gwgroup_name == "") {
      $stmt = $conn->prepare("SELECT HOUR(TIME(time)) AS hour, COUNT(connection_id) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) GROUP BY HOUR(TIME(time)) ORDER BY count DESC");
      $stmt->bind_param("ss", $start_date, $end_date);
    } else {
      $stmt = $conn->prepare("SELECT HOUR(TIME(time)) AS hour, COUNT(connection_id) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ? GROUP BY HOUR(TIME(time)) ORDER BY count DESC");
      $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $peak_hour = $row['hour'];

    //Get data to Draw Chart : Connections By Time
    $label_connection_by_time = "";
    for ($i=0; $i<24; $i++)
      $label_connection_by_time = $label_connection_by_time. '"' . $i . '", ';
    $label_connection_by_time = substr($label_connection_by_time, 0, -2);
    $label_connection_by_time = "[" . $label_connection_by_time . "]";

    $connections_by_time = [];
    for ($i=0; $i<24; $i++)
      $connections_by_time[$i] = 0;   

    if ($gwgroup_name == "") {
      $stmt = $conn->prepare("SELECT HOUR(TIME(time)) AS hour, COUNT(connection_id) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) GROUP BY HOUR(TIME(time))" );
      $stmt->bind_param("ss", $start_date, $end_date);
    } else {
      $stmt = $conn->prepare("SELECT HOUR(TIME(time)) AS hour, COUNT(connection_id) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ? GROUP BY HOUR(TIME(time))" );
      $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
    }     
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $hour = $row['hour'];
        $connections_by_time[$hour] = $row['count'];
      }
    }    
    $data_connection_by_time = "";
    for ($i=0; $i<24; $i++)
      $data_connection_by_time .= $connections_by_time[$i] . ", ";
    $data_connection_by_time = substr($data_connection_by_time, 0, -2);
    $data_connection_by_time = "[" . $data_connection_by_time . "]";

    //Get Data to draw chart : All Connections
    $label_connections = "";
    for ($i=date("d", strtotime($start_date)); $i<date("d", strtotime($end_date . "- 1 days")); $i++)
      $label_connections = $label_connections. '"' . $i . '", ';
    $label_connections = substr($label_connections, 0, -2);
    $label_connections = "[" . $label_connections . "]";
    
    $connections = [];
    for ($i=date("d", strtotime($start_date)); $i<date("d", strtotime($end_date . "- 1 days"));$i++)
      $connections[$i] = 0;   

    if ($gwgroup_name == "") {
      $stmt = $conn->prepare("SELECT DAY(DATE(time)) AS day, COUNT(connection_id) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) GROUP BY DAY(DATE(time))" );
      $stmt->bind_param("ss", $start_date, $end_date);
    } else {
      $stmt = $conn->prepare("SELECT DAY(DATE(time)) AS day, COUNT(connection_id) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ? GROUP BY DAY(DATE(time))" );
      $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
    }     
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $day = $row['day'];
        $connections[$day] = $row['count'];
      }
    }    
    $data_connections = "";
    for ($i=date("d", strtotime($start_date)); $i<date("d", strtotime($end_date . "- 1 days")); $i++)
      $data_connections .= $connections[$i] . ", ";
    $data_connections = substr($data_connections, 0, -2);
    $data_connections = "[" . $data_connections . "]";

    $unique = [];
    for ($i=date("d", strtotime($start_date)); $i<date("d", strtotime($end_date . "- 1 days"));$i++)
      $unique[$i] = 0;   

    if ($gwgroup_name == "") {
      $stmt = $conn->prepare("SELECT DAY(DATE(time)) AS day, COUNT(DISTINCT(mac)) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) GROUP BY DAY(DATE(time))" );
      $stmt->bind_param("ss", $start_date, $end_date);
    } else {
      $stmt = $conn->prepare("SELECT DAY(DATE(time)) AS day, COUNT(DISTINCT(mac)) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ? GROUP BY DAY(DATE(time))" );
      $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
    }     
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $day = $row['day'];
        $unique[$day] = $row['count'];
      }
    }    
    $data_unique_connections = "";
    for ($i=date("d", strtotime($start_date)); $i<date("d", strtotime($end_date . "- 1 days")); $i++)
      $data_unique_connections .= $unique[$i] . ", ";
    $data_unique_connections = substr($data_unique_connections, 0, -2);
    $data_unique_connections = "[" . $data_unique_connections . "]";


    //Get Data to draw chart : Device
    $label_devices = "";
    $data_devices = "";
    if ($gwgroup_name == "") {
      $stmt = $conn->prepare("SELECT device, COUNT(device) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND NOT device = 'unknown' GROUP BY device ORDER BY count DESC");
      $stmt->bind_param("ss", $start_date, $end_date);
    } else {
      $stmt = $conn->prepare("SELECT device, COUNT(device) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?  AND NOT device = 'unknown' GROUP BY device ORDER BY count DESC");
      $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
    }     
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $label_devices .= '"' . $row['device'] . '", ';
        $data_devices .= '"' . $row['count'] . '", ';
      }
    }    
    $label_devices = substr($label_devices, 0, -2);
    $label_devices = "[" . $label_devices . "]";   

    $data_devices = substr($data_devices, 0, -2);
    $data_devices = "[" . $data_devices . "]";

    //Get Data to draw chart : OS
    $label_os = "";
    $data_os = "";
    if ($gwgroup_name == "") {
      $stmt = $conn->prepare("SELECT os, COUNT(os) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?))  AND NOT os = 'unknown' GROUP BY os ORDER BY count DESC");
      $stmt->bind_param("ss", $start_date, $end_date);
    } else {
      $stmt = $conn->prepare("SELECT os, COUNT(os) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?  AND NOT os = 'unknown' GROUP BY os ORDER BY count DESC");
      $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
    }     
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $label_os .= '"' . $row['os'] . '", ';
        $data_os .= '"' . $row['count'] . '", ';
      }
    }    
    $label_os = substr($label_os, 0, -2);
    $label_os = "[" . $label_os . "]";   

    $data_os = substr($data_os, 0, -2);
    $data_os = "[" . $data_os . "]";

    //Get Data to draw chart : Browser
    $label_browser = "";
    $data_browser = "";
    if ($gwgroup_name == "") {
      $stmt = $conn->prepare("SELECT browser, COUNT(browser) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?))  AND NOT browser = 'unknown' GROUP BY browser ORDER BY count DESC");
      $stmt->bind_param("ss", $start_date, $end_date);
    } else {
      $stmt = $conn->prepare("SELECT browser, COUNT(browser) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ? AND NOT browser = 'unknown' GROUP BY browser ORDER BY count DESC");
      $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
    }     
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $label_browser .= '"' . $row['browser'] . '", ';
        $data_browser .= '"' . $row['count'] . '", ';
      }
    }    
    $label_browser = substr($label_browser, 0, -2);
    $label_browser = "[" . $label_browser . "]";   

    $data_browser = substr($data_browser, 0, -2);
    $data_browser = "[" . $data_browser . "]";
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Title -->
  <title>MyWifi</title>

  <!-- Required Meta Tags Always Come First -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <!-- Favicon -->
  <link rel="shortcut icon" href="../favicon.ico">
  <!-- Google Fonts -->
  <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans%3A400%2C300%2C500%2C600%2C700%7CPlayfair+Display%7CRoboto%7CRaleway%7CSpectral%7CRubik">
  <!-- CSS Global Compulsory -->
  <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
  <!-- CSS Global Icons -->
  <link rel="stylesheet" href="assets/vendor/icon-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/vendor/icon-line/css/simple-line-icons.css">
  <link rel="stylesheet" href="assets/vendor/icon-etlinefont/style.css">
  <link rel="stylesheet" href="assets/vendor/icon-line-pro/style.css">
  <link rel="stylesheet" href="assets/vendor/icon-hs/style.css">

  <link rel="stylesheet" href="assets/vendor/hs-admin-icons/hs-admin-icons.css">

  <link rel="stylesheet" href="assets/vendor/animate.css">
  <link rel="stylesheet" href="assets/vendor/malihu-scrollbar/jquery.mCustomScrollbar.min.css">

  <link rel="stylesheet" href="assets/vendor/flatpickr/dist/css/flatpickr.min.css">
  <link rel="stylesheet" href="assets/vendor/bootstrap-select/css/bootstrap-select.min.css">

  <link rel="stylesheet" href="assets/vendor/chartist-js/chartist.min.css">
  <link rel="stylesheet" href="assets/vendor/chartist-js-tooltip/chartist-plugin-tooltip.css">
  <link rel="stylesheet" href="assets/vendor/fancybox/jquery.fancybox.min.css">

  <link rel="stylesheet" href="assets/vendor/hamburgers/hamburgers.min.css">

  <!-- CSS Unify -->
  <link rel="stylesheet" href="assets/css/unify-admin.css">

  <!-- CSS Customization -->
  <link rel="stylesheet" href="assets/css/custom.css">

  <!-- ChartJS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js"></script>
</head>

<body>
  <!-- Header -->
  <?php include "includes/_header.php"; ?>
  <!-- End Header -->


  <main class="container-fluid px-0 g-pt-65">
    <div class="row no-gutters g-pos-rel g-overflow-hidden">
      <!-- Sidebar Nav -->
      <?php include "includes/_sidenav.php"; ?>
      <!-- End Sidebar Nav -->

      <!-- Main Code -->
      <div class="col g-ml-45 g-ml-0--lg g-pb-65--md">
        <div class="g-pa-20">
          <h1>Thống kê </h1>        
          <form method="GET" action="#">  
            <div class="row">
              <div class="col-4">
                <div class="p-1 pl-3 bg-danger text-white">
                  <?php
                    switch($period) {
                      case "day":
                        echo date("d-m-Y");
                        break;
                      case "week":
                        $today = date("d-m-Y");
                        $day_of_week = date("w", strtotime($today));
                        echo "Từ &nbsp&nbsp" . date("d-m-Y", strtotime($today . " - ".($day_of_week - 1)."days")) . "&nbsp&nbspĐến&nbsp&nbsp" . date("d-m-Y", strtotime($today . " + ".(7 - $day_of_week)."days")) . "<br>";
                        break;
                      case "month":
                        $month = date("m");
                        $year = date("Y");
                        $days_of_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        echo "Từ &nbsp&nbsp01-".$month."-".$year."&nbsp&nbspĐến&nbsp&nbsp".$days_of_month."-".$month."-".$year;
                    }
                  ?>
                </div>
              </div>    
 
              <div class="col-4">
                <div class="from-group">
                  <select name="period" class="form-control">
                    <option value="day" <?php if ($period == "day") echo "selected" ?>>Ngày hôm nay</option>
                    <option value="week" <?php if ($period == "week") echo "selected" ?>>Tuần</option>
                    <option value="month" <?php if ($period == "month") echo "selected" ?>>Tháng</option>
                  </select>
                </div>
              </div>

              <div class="col-4">              
                <div class="form-row">
                  <div class="from-group col-sm-10">
                    <select name="gwgroup_name" class="form-control">
                      <?php 
                        $stmt = $conn->prepare("SELECT * FROM gateway_groups");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                      ?>
                          <option value="<?php echo $row['gwgroup_name'];?>" <?php 
                          if (isset($_GET['gwgroup_name']))
                           if ($row['gwgroup_name'] == $_GET['gwgroup_name']) echo "selected" ?>><?php echo $row['gwgroup_name']; ?>
                           </option>
                      <?php
                        }
                      ?>
                          <option value="" <?php if (!isset($_GET['gwgroup_name'])) echo "selected" ?>>Tất cả</option>
                    </select>
                  </div>
                  <div class="from-group col-sm-2">
                      <button type="submit" class="btn btn-primary"><i class="hs-admin-filter"></i></button>
                  </div>
                </div>                
              </div>
            </div>   
          </form>
        </div>

        <div class="g-pa-20">
          <div class="row">
            <div class="col-sm-6 col-lg-6 col-xl-4 g-mb-30">
              <!-- Project Card -->
              <div class="card g-brd-gray-light-v7 h-100 text-center g-pa-15 g-pa-25-30--md">
                <header class="media g-mb-40">
                  <h3 class="d-flex align-self-center text-uppercase g-font-size-12 g-font-size-default--md g-color-black mb-0">Hình thức đăng nhập</h3>
                </header>

                <section class="g-mb-40">
                  <div class="js-donut-chart g-pos-rel mx-auto" style="width: 128px; height: 128px;" data-series='[<?php echo $graph_connection_type_data; ?>]' data-border-width="4" data-start-angle="0" data-fill-colors='["#3dd1e8","#1d75e5","#e62154","#72c02c", "#ebc71d", "#9a69cb"]'>
                    <i class="hs-admin-clipboard g-absolute-centered g-font-size-40 g-color-gray-light-v3"></i>
                  </div>
                </section>

                <section class="row">
                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-lightblue-v3 g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $percent_image_video;?>
                      <span class="g-font-size-default g-valign-top">%</span>
                    </strong>
                      <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">Hình ảnh/Video</span>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-darkblue-v2 g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $percent_local_account;?>
                      <span class="g-font-size-default g-valign-top">%</span>
                    </strong>
                      <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">Tài khoản</span>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-primary g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $percent_social;?>
                      <span class="g-font-size-default g-valign-top">%</span>
                    </strong>
                      <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">Mạng xã hội</span>
                    </div>
                  </div>
                </section>

                <section class="row">
                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-green g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $percent_sms;?>
                      <span class="g-font-size-default g-valign-top">%</span>
                    </strong>
                      <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">SMS</span>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-yellow g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $percent_survey;?>
                      <span class="g-font-size-default g-valign-top">%</span>
                    </strong>
                      <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">Khảo sát</span>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-purple g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $percent_voucher;?>
                      <span class="g-font-size-default g-valign-top">%</span>
                    </strong>
                      <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">Voucher</span>
                    </div>
                  </div>
                </section>
              </div>
              <!-- End Project Card -->
            </div>

            <div class="col-sm-6 col-lg-6 col-xl-4 g-mb-30">
              <!-- Income Project Card -->
              <div class="card g-brd-gray-light-v7 h-100 ">
                <header class="media g-pa-15 g-pa-25-30-0--md g-mb-20">
                  <h3 class="d-flex align-self-center text-uppercase g-font-size-12 g-font-size-default--md g-color-black mb-0">Kết nối</h3>
                </header>

                <div class="g-pa-15 g-pa-0-30-25--md">
                  <section class="media g-mb-20">
                    <div class="media-body align-self-end g-line-height-1_3 g-font-weight-300 g-font-size-40 g-color-black">
                      <?php echo $total_connections; ?>
                      <span class="g-font-size-16 g-color-gray-dark-v7">Kết nối</span>
                      <h5 class="g-font-weight-300 g-font-size-default g-font-size-16--md g-color-gray-dark-v6 mb-0"><?php echo $compare_total_connections;?></h5>
                    </div>
                  </section>

                  <section class="media g-mb-20">
                    <div class="media-body align-self-end g-line-height-1_3 g-font-weight-300 g-font-size-40 g-color-black">
                      <?php echo $unique_connections; ?>
                      <span class="g-font-size-16 g-color-gray-dark-v7">Người dùng</span>
                      <h5 class="g-font-weight-300 g-font-size-default g-font-size-16--md g-color-gray-dark-v6 mb-0"><?php echo $compare_unique_connections;?></h5>
                    </div>
                  </section>

                  <section class="media g-mb-20">
                    <div class="media-body align-self-end g-line-height-1_3 g-font-weight-300 g-font-size-30 g-color-black">
                      <?php if ($peak_day != "" ) echo $peak_day . "-" . date("m-Y",strtotime($start_date)) ; else echo "-"; ?>
                      <h5 class="g-font-weight-300 g-font-size-default g-font-size-16--md g-color-gray-dark-v6 mb-0">Ngày kết nối nhiều nhất</h5>
                    </div>
                  </section>

                  <section class="media g-mb-20">
                    <div class="media-body align-self-end g-line-height-1_3 g-font-weight-300 g-font-size-30 g-color-black">
                      <?php if ($peak_hour != "" ) echo $peak_hour . " - " . ($peak_hour + 1) . "h" ; else echo "-";?>
                      <h5 class="g-font-weight-300 g-font-size-default g-font-size-16--md g-color-gray-dark-v6 mb-0">Thời điểm kết nối nhiều nhất</h5>
                    </div>
                  </section>
                </div>
              </div>
              <!-- End Income Project Card -->
            </div>

            <div class="col-sm-6 col-lg-6 col-xl-4 g-mb-30">
              <!-- Panel -->
              <div class="card h-100 g-brd-gray-light-v7 rounded">
                <div class="card-block g-pa-20">
                  <header class="media g-mb-40">
                    <h4 class="d-flex align-self-center text-uppercase g-font-size-12 g-font-size-default--md g-color-black mb-0">Kết nối theo giờ</h4>
                  </header>                  

                  <canvas id="chart_connection_by_time"></canvas>
                </div>
              </div>
              <!-- End Panel -->
            </div>

            <!-- Statistic Card -->
            <div class="col-xl-12">
              <!-- Statistic Card -->
              <div class="card g-brd-gray-light-v7 g-pa-15 g-pa-25-30--md g-mb-30" >
                <header class="media g-mb-30">
                  <h3 class="d-flex align-self-center text-uppercase g-font-size-12 g-font-size-default--md g-color-black mb-0">Thống kê kết nối theo ngày</h3>         
                </header>

                <section>
                  <ul class="list-unstyled d-flex g-mb-45">                    
                    <li class="media g-ml-5 g-ml-35--md">
                      <div class="d-flex align-self-center g-mr-8">
                        <span class="u-badge-v2--md g-pos-stc g-transform-origin--top-left g-bg-darkblue-v2"></span>
                      </div>

                      <div class="media-body align-self-center g-font-size-12 g-font-size-default--md">Kết nối</div>
                    </li>
                    <li class="media g-ml-5 g-ml-35--md">
                      <div class="d-flex align-self-center g-mr-8">
                        <span class="u-badge-v2--md g-pos-stc g-transform-origin--top-left g-bg-yellow"></span>
                      </div>

                      <div class="media-body align-self-center g-font-size-12 g-font-size-default--md">Người dùng</div>
                    </li>
                  </ul>

                  <canvas id="chart_connections" style="display: block; height: 200px"></canvas>
                </section>
              </div>
              <!-- End Statistic Card -->
            </div>
            <!-- End Statistic Card -->
            <div class="col-xl-4">
              <div class="card g-brd-gray-light-v7 g-pa-15 g-pa-25-30--md g-mb-30">
                <h5>Thống kê thiết bị</h5>

                <?php
                  //Get Top 5 devices
                  if ($gwgroup_name == "") {
                    $stmt = $conn->prepare("SELECT device, COUNT(device) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND NOT device = 'unknown' GROUP BY device ORDER BY count DESC LIMIT 5");
                    $stmt->bind_param("ss", $start_date, $end_date);
                  } else {
                    $stmt = $conn->prepare("SELECT device, COUNT(device) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?  AND NOT device = 'unknown' GROUP BY device ORDER BY count DESC LIMIT 5");
                    $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
                  }     
                  $stmt->execute();
                  $result = $stmt->get_result();
                  while ($row = $result->fetch_assoc()) {
                ?>
                    
                      <div class="row my-2">
                        <div class="col-10">
                          <?php echo $row['device']; ?>
                        </div>
                        <div class="col-2">
                          <?php echo $row['count']; ?>
                        </div>
                      </div>
                <?php
                  }
                ?>
              </div>
            </div>

            <div class="col-xl-8">
              <div class="card g-brd-gray-light-v7 g-pa-15 g-pa-25-30--md g-mb-30">
                <canvas id="chart_devices"></canvas>
              </div>
            </div>

            <div class="col-xl-8">
              <div class="card g-brd-gray-light-v7 g-pa-15 g-pa-25-30--md g-mb-30">
                <canvas id="chart_os"></canvas>
              </div>
            </div>

            <div class="col-xl-4">
              <div class="card g-brd-gray-light-v7 g-pa-15 g-pa-25-30--md g-mb-30">
                <h5>Thống kê Hệ điều hành</h5>          

                <?php
                 //Get Top 5 OS
                  if ($gwgroup_name == "") {
                    $stmt = $conn->prepare("SELECT os, COUNT(os) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND NOT os = 'unknown' GROUP BY os ORDER BY count DESC LIMIT 5");
                    $stmt->bind_param("ss", $start_date, $end_date);
                  } else {
                    $stmt = $conn->prepare("SELECT os, COUNT(os) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?  AND NOT os = 'unknown' GROUP BY os ORDER BY count DESC LIMIT 5");
                    $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
                  }     
                  $stmt->execute();
                  $result = $stmt->get_result();
                  while ($row = $result->fetch_assoc()) {
                ?>
                    
                      <div class="row my-2">
                        <div class="col-9">
                          <?php echo $row['os']; ?>
                        </div>
                        <div class="col-3">
                          <?php echo $row['count']; ?>
                        </div>
                      </div>
                <?php
                  }
                ?>    
              </div>
            </div>
           


            <div class="col-xl-4">
              <div class="card g-brd-gray-light-v7 g-pa-15 g-pa-25-30--md g-mb-30">
                <h5>Thống kê Trình duyệt</h5>   
                <?php
                 //Get Top 5 Browser
                  if ($gwgroup_name == "") {
                    $stmt = $conn->prepare("SELECT browser, COUNT(browser) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND NOT browser = 'unknown' GROUP BY browser ORDER BY count DESC LIMIT 5");
                    $stmt->bind_param("ss", $start_date, $end_date);
                  } else {
                    $stmt = $conn->prepare("SELECT browser, COUNT(browser) AS count FROM history WHERE (time BETWEEN DATE(?) AND DATE(?)) AND gwgroup_name = ?  AND NOT browser = 'unknown' GROUP BY browser ORDER BY count DESC LIMIT 5");
                    $stmt->bind_param("sss", $start_date, $end_date, $gwgroup_name);
                  }     
                  $stmt->execute();
                  $result = $stmt->get_result();
                  while ($row = $result->fetch_assoc()) {
                ?>
                    
                      <div class="row my-2">
                        <div class="col-9">
                          <?php echo $row['browser']; ?>
                        </div>
                        <div class="col-3">
                          <?php echo $row['count']; ?>
                        </div>
                      </div>
                <?php
                  }
                ?>                 
              </div>
            </div>

            <div class="col-xl-8">
              <div class="card g-brd-gray-light-v7 g-pa-15 g-pa-25-30--md g-mb-30">
                <canvas id="chart_browser"></canvas>
              </div>
            </div>            
          </div>
        </div>

        <!-- Footer -->
        <?php include "includes/_footer.php"; ?>
        <!-- End Footer -->
      </div>
    </div>
  </main>

  <!-- JS Global Compulsory -->
  <script src="assets/vendor/jquery/jquery.min.js"></script>
  <script src="assets/vendor/jquery-migrate/jquery-migrate.min.js"></script>

  <script src="assets/vendor/popper.min.js"></script>
  <script src="assets/vendor/bootstrap/bootstrap.min.js"></script>

  <script src="assets/vendor/cookiejs/jquery.cookie.js"></script>


  <!-- jQuery UI Core -->
  <script src="assets/vendor/jquery-ui/ui/widget.js"></script>
  <script src="assets/vendor/jquery-ui/ui/version.js"></script>
  <script src="assets/vendor/jquery-ui/ui/keycode.js"></script>
  <script src="assets/vendor/jquery-ui/ui/position.js"></script>
  <script src="assets/vendor/jquery-ui/ui/unique-id.js"></script>
  <script src="assets/vendor/jquery-ui/ui/safe-active-element.js"></script>

  <!-- jQuery UI Helpers -->
  <script src="assets/vendor/jquery-ui/ui/widgets/menu.js"></script>
  <script src="assets/vendor/jquery-ui/ui/widgets/mouse.js"></script>

  <!-- jQuery UI Widgets -->
  <script src="assets/vendor/jquery-ui/ui/widgets/datepicker.js"></script>

  <!-- JS Plugins Init. -->
  <script src="assets/vendor/appear.js"></script>
  <script src="assets/vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
  <script src="assets/vendor/flatpickr/dist/js/flatpickr.min.js"></script>
  <script src="assets/vendor/malihu-scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
  <script src="assets/vendor/chartist-js/chartist.min.js"></script>
  <script src="assets/vendor/chartist-js-tooltip/chartist-plugin-tooltip.js"></script>
  <script src="assets/vendor/fancybox/jquery.fancybox.min.js"></script>

  <!-- JS Unify -->
  <script src="assets/js/hs.core.js"></script>
  <script src="assets/js/helpers/hs.hamburgers.js"></script>
  <script src="assets/js/components/hs.datepicker.js"></script>
  <script src="assets/js/components/hs.dropdown.js"></script>
  <script src="assets/js/components/hs.scrollbar.js"></script>
  <script src="assets/js/helpers/hs.focus-state.js"></script>
  <script src="assets/js/components/hs.dropdown.js"></script>
  <script src="assets/js/components/hs.side-nav.js"></script>
  <script src="assets/js/components/hs.range-datepicker.js"></script>
  <script src="assets/js/components/hs.area-chart.js"></script>
  <script src="assets/js/components/hs.donut-chart.js"></script>
  <script src="assets/js/components/hs.bar-chart.js"></script>
  <script src="assets/js/components/hs.popup.js"></script>

  <!-- JS Custom -->
  <script src="assets/js/custom.js"></script>

  <!-- JS Plugins Init. -->
  <script>
    $(document).on('ready', function () {
      // initialization of custom select
      $('.js-select').selectpicker();
  
      // initialization of hamburger
      $.HSCore.helpers.HSHamburgers.init('.hamburger');
  
      // initialization of charts
      $.HSCore.components.HSAreaChart.init('.js-area-chart');
      $.HSCore.components.HSDonutChart.init('.js-donut-chart');
      $.HSCore.components.HSBarChart.init('.js-bar-chart');
  
      // initialization of sidebar navigation component
      $.HSCore.components.HSSideNav.init('.js-side-nav', {
        afterOpen: function() {
          setTimeout(function() {
            $.HSCore.components.HSAreaChart.init('.js-area-chart');
            $.HSCore.components.HSDonutChart.init('.js-donut-chart');
            $.HSCore.components.HSBarChart.init('.js-bar-chart');
          }, 400);
        },
        afterClose: function() {
          setTimeout(function() {
            $.HSCore.components.HSAreaChart.init('.js-area-chart');
            $.HSCore.components.HSDonutChart.init('.js-donut-chart');
            $.HSCore.components.HSBarChart.init('.js-bar-chart');
          }, 400);
        }
      });
  
      // initialization of HSDropdown component
      $.HSCore.components.HSDropdown.init($('[data-dropdown-target]'), {
        dropdownHideOnScroll: false,
        dropdownType: 'css-animation',
        dropdownAnimationIn: 'fadeIn',
        dropdownAnimationOut: 'fadeOut'
      });
  
      // initialization of range datepicker
      $.HSCore.components.HSRangeDatepicker.init('#rangeDatepicker, #rangeDatepicker2, #rangeDatepicker3');
  
      // initialization of datepicker
      $.HSCore.components.HSDatepicker.init('#datepicker', {
        dayNamesMin: [
          'SU',
          'MO',
          'TU',
          'WE',
          'TH',
          'FR',
          'SA'
        ]
      });
  
      // initialization of HSDropdown component
      $.HSCore.components.HSDropdown.init($('[data-dropdown-target]'), {dropdownHideOnScroll: false});
  
      // initialization of custom scrollbar
      $.HSCore.components.HSScrollBar.init($('.js-custom-scroll'));
  
      // initialization of popups
      $.HSCore.components.HSPopup.init('.js-fancybox', {
        btnTpl: {
          smallBtn: '<button data-fancybox-close class="btn g-pos-abs g-top-25 g-right-30 g-line-height-1 g-bg-transparent g-font-size-16 g-color-gray-light-v3 g-brd-none p-0" title=""><i class="hs-admin-close"></i></button>'
        }
      });
    });
  </script>


  <script type="text/javascript">
    var ctx = document.getElementById("chart_connection_by_time").getContext('2d');
    var color = Chart.helpers.color;
    var chart_connection_by_time = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo $label_connection_by_time; ?>,
            datasets: [{
                label: 'Kết nối',
                data: <?php echo $data_connection_by_time; ?>,
                backgroundColor: 'rgba(255, 0, 0, 1)',
            }]
        },
        options: {
          responsive: true,
          legend: {
            display: false,
          },
          title: {
            display: false,
          },
          scales: {
            xAxes: [{
              display: true,
              gridLines: {
                display: false
              }
            }],
            yAxes: [{
              display: false,
              gridLines: {
                display: false
              }
            }]
          }
        }
    });
  </script>

  <script type="text/javascript">
    var ctx_connections = document.getElementById("chart_connections").getContext('2d');
    var chart_connections = new Chart(ctx_connections, {
        type: 'line',
        data: {
            labels: <?php echo $label_connections; ?>,
            datasets: [
              {
                label: 'Kết nối',
                data: <?php echo $data_connections; ?>,
                backgroundColor: 'rgba(0, 0, 255, 0.2)',
                borderColor: 'rgba(0, 0, 255, 0.8)'
              },
              {
                label: 'Người dùng',
                data: <?php echo $data_unique_connections; ?>,
                backgroundColor: 'rgba(255, 195, 0, 0.2)',
                borderColor: 'rgba(255, 195, 0, 0.8)'
              }
            ]
        },
        options: {
          responsive: true,
          legend: {
            display: false,
          },
          title: {
            display: false,
          },
          scales: {
            xAxes: [{
              display: true,
              gridLines: {
                display: false
              }
            }],
            yAxes: [{
              display: true,
              gridLines: {
                display: false
              }
            }]
          }
        }
    });
  </script>

  <script type="text/javascript">
    var ctx_devices = document.getElementById("chart_devices").getContext('2d');
    var chart_devices = new Chart(ctx_devices, {
        type: 'pie',
        data: {
            labels: <?php echo $label_devices; ?>,
            datasets: [
              {
                label: 'Thiết bị',
                data: <?php echo $data_devices; ?>,
                backgroundColor: [
                  'rgba(54, 162, 235, 1)',
                  'rgba(255, 206, 86, 1)',
                  'rgba(75, 192, 192, 1)',
                  'rgba(153, 102, 255, 1)',
                  'rgba(255, 159, 64, 1)',
                  'rgba(255, 99, 132, 1)',
                  'rgba(54, 162, 235, 1)',
                  'rgba(255, 206, 86, 1)',
                  'rgba(75, 192, 192, 1)',
                  'rgba(153, 102, 255, 1)',
                  'rgba(255, 159, 64, 1)',
                  'rgba(255, 99, 132, 1)'
                ],
                borderColor: [
                  'rgba(255, 255, 255, 1)'
                ]
              }]
        },
        options: {
          responsive: true,
          legend: {
            display: true
          }
        }
    });
  </script>

  <script type="text/javascript">
    var ctx_os = document.getElementById("chart_os").getContext('2d');
    var chart_os = new Chart(ctx_os, {
        type: 'pie',
        data: {
            labels: <?php echo $label_os; ?>,
            datasets: [
              {
                label: 'Thiết bị',
                data: <?php echo $data_os; ?>,
                backgroundColor: [
                  'rgba(54, 162, 235, 1)',
                  'rgba(255, 206, 86, 1)',
                  'rgba(75, 192, 192, 1)',
                  'rgba(153, 102, 255, 1)',
                  'rgba(255, 159, 64, 1)',
                  'rgba(255, 99, 132, 1)',
                  'rgba(54, 162, 235, 1)',
                  'rgba(255, 206, 86, 1)',
                  'rgba(75, 192, 192, 1)',
                  'rgba(153, 102, 255, 1)',
                  'rgba(255, 159, 64, 1)',
                  'rgba(255, 99, 132, 1)'
                ],
                borderColor: [
                  'rgba(255, 255, 255, 1)'
                ]
              }]
        },
        options: {
          responsive: true,
          legend: {
            display: true
          }
        }
    });
  </script>

  <script type="text/javascript">
    var ctx_browser = document.getElementById("chart_browser").getContext('2d');
    var chart_browser = new Chart(ctx_browser, {
        type: 'pie',
        data: {
            labels: <?php echo $label_browser; ?>,
            datasets: [
              {
                label: 'Thiết bị',
                data: <?php echo $data_browser; ?>,
                backgroundColor: [
                  'rgba(54, 162, 235, 1)',
                  'rgba(255, 206, 86, 1)',
                  'rgba(75, 192, 192, 1)',
                  'rgba(153, 102, 255, 1)',
                  'rgba(255, 159, 64, 1)',
                  'rgba(255, 99, 132, 1)',
                  'rgba(54, 162, 235, 1)',
                  'rgba(255, 206, 86, 1)',
                  'rgba(75, 192, 192, 1)',
                  'rgba(153, 102, 255, 1)',
                  'rgba(255, 159, 64, 1)',
                  'rgba(255, 99, 132, 1)'
                ],
                borderColor: [
                  'rgba(255, 255, 255, 1)'
                ]
              }]
        },
        options: {
          responsive: true,
          legend: {
            display: true
          }
        }
    });
  </script>
</body>

</html>

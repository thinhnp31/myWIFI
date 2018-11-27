<!-- set bodyTheme = "u-card-v1" -->
<?php 
    include "../includes/defines.php";  
    include '../includes/db_connect.php'; 

    //Check if this client has been logged in or not
    $logged_in = false;

  
    if (isset($_SESSION['email'])) { //if this client still has a session
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

    if (!$logged_in) {
        $conn->close();
        header("Location: " . mywifi_admin . "login.php");
    }
    $current_year = date('Y');
    $current_month = date('m');

    if ($current_month == 1) {
      $last_month = 12;
      $last_year = $current_year - 1;
    } else {
      $last_month = $current_month - 1;
      $last_year = $current_year;
    }
    //Get information from `connections` table
    //Get number of connections in this month
    $stmt = $conn->prepare("SELECT COUNT(connection_id) AS total FROM `connections` WHERE YEAR(time) = " . $current_year . " AND MONTH(time) = " . $current_month);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_connections = $row['total'];
    //Get number of connections in last month
    $stmt = $conn->prepare("SELECT COUNT(connection_id) AS total FROM `connections` WHERE YEAR(time) = " . $last_year . " AND MONTH(time) = " . $last_month);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $delta_total_connections = $total_connections - $row['total'];

    //get data to draw a mini graph    
    $stmt = $conn->prepare("SELECT COUNT(connection_id) as count, CAST(time as DATE) as date FROM connections WHERE YEAR(time) = " . $current_year . " AND MONTH(time) = " . $current_month . " GROUP BY CAST(time as DATE)");
    $stmt->execute();
    $result = $stmt->get_result();
    $connection_series =[];
    $connection_labels =[];
    $days = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
    for ($i = 0; $i < $days; $i++) {
      array_push($connection_series, 0);
      array_push($connection_labels, strval($i + 1));
    }
    $max_connections = 0;
    while ($row = $result->fetch_assoc()) {
        $temp_series = $row['count'];
        $temp_labels = date("d", strtotime($row['date']));
        if ($temp_series > $max_connections) $max_connections = $temp_series;        
        for ($i = 0; $i < $days; $i++) {
          if ($temp_labels == $connection_labels[$i]) {
            $connection_series[$i] = $temp_series;            
          }
        }
    }
    array_push($connection_series, $connection_series[$days-1]);
    array_push($connection_labels, $days); 

    //Get number of clients of this month 
    $stmt = $conn->prepare("SELECT DISTINCT mac FROM `connections` WHERE YEAR(time) = " . $current_year . " AND MONTH(time) = " . $current_month);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_clients = $result->num_rows;

    //Get number of clients of last month 
    $stmt = $conn->prepare("SELECT DISTINCT mac FROM `connections` WHERE YEAR(time) = " . $last_year . " AND MONTH(time) = " . $last_year);
    $stmt->execute();
    $result = $stmt->get_result();
    $delta_total_clients = $total_clients - $result->num_rows;

    //get data to draw a mini graph
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT mac) count, CAST(time as DATE) as date FROM connections WHERE YEAR(time) = " . $current_year. " AND MONTH(time) = " . $current_month. " GROUP BY CAST(time as DATE)");
    $stmt->execute();
    $result = $stmt->get_result();
    $client_series =[];
    $client_labels =[];
    $days = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
    for ($i = 0; $i < $days; $i++) {
      array_push($client_series, 0);
      array_push($client_labels, strval($i + 1)); 
    }    
    $max_clients = 0;
    while ($row = $result->fetch_assoc()) {
        $temp_series = $row['count'];
        $temp_labels = date("d", strtotime($row['date']));
        if ($temp_series > $max_clients) $max_clients = $temp_series;
        for ($i = 0; $i < $days; $i++) {
          if ($temp_labels == $client_labels[$i]) {
            $client_series[$i] = $temp_series;            
          }
        }
    }
    array_push($client_series, $client_series[$days-1]);
    array_push($client_labels, $days); 

    //Get top os 
    $stmt = $conn->prepare("SELECT os, COUNT(os) AS count FROM connections WHERE NOT os = 'unknown' GROUP BY os ORDER BY count DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $top_os = $row['os'];
    $number_top_os = $row['count'];

    //Get top device 
    $stmt = $conn->prepare("SELECT device, COUNT(device) AS count FROM connections WHERE NOT device = 'unknown' GROUP BY device ORDER BY count DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $top_device = $row['device'];
    $number_top_device = $row['count'];

    //Get top browser 
    $stmt = $conn->prepare("SELECT browser, COUNT(browser) AS count FROM connections WHERE NOT browser = 'unknown' GROUP BY browser ORDER BY count DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $top_browser = $row['browser'];
    $number_top_browser = $row['count'];

    //Get top connection day 
    $stmt = $conn->prepare("SELECT COUNT(connection_id) count, CAST(time as DATE) as date FROM connections WHERE YEAR(time) = " . $current_year. " AND MONTH(time) = " . $current_month. " GROUP BY CAST(time as DATE) ORDER BY count DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $top_connection_day = $row['date'];
    $number_top_connection_day = $row['count'];

    //Get top connection hour 
    $stmt = $conn->prepare("SELECT COUNT(connection_id) as count, HOUR(time) as hour FROM connections  WHERE YEAR(time) = " . $current_year. " AND MONTH(time) = " . $current_month. " GROUP BY HOUR(time)");
    $stmt->execute();
    $result = $stmt->get_result();
    $connection_hour_series =[];
    $connection_hour_labels =[];
    $max_hour = 0;
    $max_connection_hour = 0;
    while ($row = $result->fetch_assoc()) {
        $temp_series = $row['count'];
        $temp_labels = $row['hour'];
        if ($temp_series >= $max_connection_hour) {
            $max_connection_hour = $temp_series;
            $max_hour = $temp_labels;
        }        
        array_push($connection_hour_series, $temp_series);
        array_push($connection_hour_labels, $temp_labels);
    }

    //Get connection type
    $stmt = $conn->prepare("SELECT connection_type, COUNT(connection_type) as count FROM `connections` WHERE YEAR(time) = " . $current_year. " AND MONTH(time) = " . $current_month. " GROUP BY connection_type ORDER BY connection_type ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $image = $row['count'];
    $row = $result->fetch_assoc();
    $local_account = $row['count'];
    $row = $result->fetch_assoc();
    $sms = $row['count'];
    $row = $result->fetch_assoc();
    $social = $row['count'];
    $row = $result->fetch_assoc();
    $survey = $row['count'];
    $row = $result->fetch_assoc();
    $video = $row['count'];
    $row = $result->fetch_assoc();
    $voucher = $row['count'];
    $imgvid = $image + $video;
    $total = $imgvid + $local_account + $sms + $social + $survey + $voucher;
    $imgvid = round($imgvid * 100 / $total);
    $local_account = round($local_account * 100 / $total);
    $sms = round($sms * 100 / $total);
    $social = round($social * 100 / $total);
    $survey = round($survey * 100 / $total);
    $voucher = 100 - $imgvid - $local_account - $sms - $social - $survey; 

    $conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Title -->
  <title>Dashboard v.3 | Unify - Responsive Website Template</title>

  <!-- Required Meta Tags Always Come First -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <!-- Favicon -->
  <link rel="shortcut icon" href="../../favicon.ico">
  <!-- Google Fonts -->
  <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans%3A400%2C300%2C500%2C600%2C700%7CPlayfair+Display%7CRoboto%7CRaleway%7CSpectral%7CRubik">
  <!-- CSS Global Compulsory -->
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/bootstrap.min.css">
  <!-- CSS Global Icons -->
  <link rel="stylesheet" href="../../assets/vendor/icon-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="../../assets/vendor/icon-line/css/simple-line-icons.css">
  <link rel="stylesheet" href="../../assets/vendor/icon-etlinefont/style.css">
  <link rel="stylesheet" href="../../assets/vendor/icon-line-pro/style.css">
  <link rel="stylesheet" href="../../assets/vendor/icon-hs/style.css">

  <link rel="stylesheet" href="../assets/vendor/hs-admin-icons/hs-admin-icons.css">

  <link rel="stylesheet" href="../../assets/vendor/animate.css">
  <link rel="stylesheet" href="../../assets/vendor/malihu-scrollbar/jquery.mCustomScrollbar.min.css">

  <link rel="stylesheet" href="../assets/vendor/flatpickr/dist/css/flatpickr.min.css">
  <link rel="stylesheet" href="../assets/vendor/bootstrap-select/css/bootstrap-select.min.css">

  <link rel="stylesheet" href="../assets/vendor/chartist-js/chartist.min.css">
  <link rel="stylesheet" href="../assets/vendor/chartist-js-tooltip/chartist-plugin-tooltip.css">
  <link rel="stylesheet" href="../../assets/vendor/fancybox/jquery.fancybox.min.css">

  <link rel="stylesheet" href="../../assets/vendor/hamburgers/hamburgers.min.css">

  <!-- CSS Unify -->
  <link rel="stylesheet" href="../assets/css/unify-admin.css">

  <!-- CSS Customization -->
  <link rel="stylesheet" href="../../assets/css/custom.css">
</head>

<body>
  <!-- Header -->
  <header id="js-header" class="u-header u-header--sticky-top">
    <div class="u-header__section u-header__section--admin-dark g-min-height-65">
      <nav class="navbar no-gutters g-pa-0">
        <div class="col-auto d-flex flex-nowrap u-header-logo-toggler g-py-12">
          <!-- Logo -->
          <a href="../index.html" class="navbar-brand d-flex align-self-center g-hidden-xs-down g-line-height-1 py-0 g-mt-5">

            <svg class="u-header-logo" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g transform="translate(-78.000000, -19.000000)">
                  <g transform="translate(78.000000, 19.000000)">
                    <path class="g-fill-primary" d="M0,0 L19.2941176,0 L19.2941176,0 C23.7123956,-8.11624501e-16 27.2941176,3.581722 27.2941176,8 L27.2941176,27.2941176 L8,27.2941176 L8,27.2941176 C3.581722,27.2941176 5.41083001e-16,23.7123956 0,19.2941176 L0,0 Z"></path>
                    <path class="g-fill-white" d="M21.036662,24.8752523 L20.5338647,22.6659916 L20.3510293,22.6659916 C19.8533083,23.4481246 19.1448284,24.0626484 18.2255681,24.5095816 C17.3063079,24.9565147 16.2575544,25.1799779 15.0792761,25.1799779 C13.0376043,25.1799779 11.5139914,24.672107 10.5083918,23.6563498 C9.50279224,22.6405927 9,21.1017437 9,19.0397567 L9,8.02392554 L12.6109986,8.02392554 L12.6109986,18.4150692 C12.6109986,19.7050808 12.8750915,20.6725749 13.4032852,21.3175807 C13.9314789,21.9625865 14.7593086,22.2850846 15.886799,22.2850846 C17.3901196,22.2850846 18.4947389,21.8356188 19.2006901,20.9366737 C19.9066413,20.0377286 20.2596117,18.5318912 20.2596117,16.4191164 L20.2596117,8.02392554 L23.855374,8.02392554 L23.855374,24.8752523 L21.036662,24.8752523 Z"></path>
                    <path class="g-fill-white" d="M44.4764678,24.4705882 L40.8807055,24.4705882 L40.8807055,14.1099172 C40.8807055,12.809748 40.6191519,11.8397145 40.096037,11.1997875 C39.5729221,10.5598605 38.7425531,10.2399018 37.6049051,10.2399018 C36.0914269,10.2399018 34.9842682,10.6868282 34.2833958,11.5806945 C33.5825234,12.4745608 33.2320924,13.9727801 33.2320924,16.0753974 L33.2320924,24.4705882 L29.6515664,24.4705882 L29.6515664,7.61926145 L32.4550421,7.61926145 L32.9578394,9.8285222 L33.1406747,9.8285222 C33.6485533,9.02607405 34.3697301,8.40647149 35.3042266,7.96969592 C36.2387232,7.53292034 37.27478,7.31453583 38.412428,7.31453583 C42.4551414,7.31453583 44.4764678,9.3714132 44.4764678,13.4852296 L44.4764678,24.4705882 Z M53.7357283,24.4705882 L50.1552023,24.4705882 L50.1552023,7.61926145 L53.7357283,7.61926145 L53.7357283,24.4705882 Z M49.9418944,3.15503112 C49.9418944,2.51510412 50.1171098,2.0224693 50.467546,1.67711187 C50.8179823,1.33175444 51.3182351,1.15907831 51.9683197,1.15907831 C52.5980892,1.15907831 53.0881846,1.33175444 53.4386208,1.67711187 C53.7890571,2.0224693 53.9642725,2.51510412 53.9642725,3.15503112 C53.9642725,3.76448541 53.7890571,4.24442346 53.4386208,4.59485968 C53.0881846,4.94529589 52.5980892,5.12051137 51.9683197,5.12051137 C51.3182351,5.12051137 50.8179823,4.94529589 50.467546,4.59485968 C50.1171098,4.24442346 49.9418944,3.76448541 49.9418944,3.15503112 Z M68.0077253,10.3313195 L63.8939294,10.3313195 L63.8939294,24.4705882 L60.2981671,24.4705882 L60.2981671,10.3313195 L57.525164,10.3313195 L57.525164,8.65532856 L60.2981671,7.55831633 L60.2981671,6.4613041 C60.2981671,4.47042009 60.7654084,2.99505497 61.699905,2.03516447 C62.6344015,1.07527397 64.0615189,0.595335915 65.9812999,0.595335915 C67.2408388,0.595335915 68.4800439,0.803563007 69.6989525,1.22002344 L68.7543031,3.93208145 C67.8705943,3.64766945 67.0275286,3.50546559 66.2250804,3.50546559 C65.4124747,3.50546559 64.820805,3.75686171 64.4500537,4.25966149 C64.0793023,4.76246128 63.8939294,5.51664965 63.8939294,6.52224922 L63.8939294,7.61926145 L68.0077253,7.61926145 L68.0077253,10.3313195 Z M69.0089215,7.61926145 L72.9094094,7.61926145 L76.3375727,17.1724096 C76.8556088,18.5335242 77.2009611,19.813359 77.3736398,21.0119524 L77.49553,21.0119524 C77.5869482,20.453286 77.7545456,19.7752783 77.9983273,18.9779089 C78.242109,18.1805396 79.5321012,14.3943616 81.8683427,7.61926145 L85.738358,7.61926145 L78.5315971,26.7103215 C77.2212704,30.2146837 75.0374253,31.9668385 71.9799963,31.9668385 C71.1877057,31.9668385 70.4157419,31.8805004 69.6640816,31.7078217 L69.6640816,28.8738734 C70.2024329,28.9957643 70.8169567,29.0567088 71.5076716,29.0567088 C73.2344587,29.0567088 74.4482703,28.056203 75.1491427,26.0551615 L75.7738303,24.4705882 L69.0089215,7.61926145 Z"></path>
                  </g>
                </g>
              </g>
            </svg>



          </a>
          <!-- End Logo -->

          <!-- Sidebar Toggler -->
          <a class="js-side-nav u-header__nav-toggler d-flex align-self-center ml-auto" href="#!" data-hssm-class="u-side-nav--mini u-sidebar-navigation-v1--mini" data-hssm-body-class="u-side-nav-mini" data-hssm-is-close-all-except-this="true" data-hssm-target="#sideNav">
            <i class="hs-admin-align-left"></i>
          </a>
          <!-- End Sidebar Toggler -->
        </div>

        <!-- Messages/Notifications/Top Search Bar/Top User -->
        <div class="col-auto d-flex g-py-12 g-pl-40--lg ml-auto">
          

          <!-- Top User -->
          <div class="col-auto d-flex g-pt-5 g-pt-0--sm g-pl-10 g-pl-20--sm">
            <div class="g-pos-rel g-px-10--lg">
              <a id="profileMenuInvoker" class="d-block" href="#!" aria-controls="profileMenu" aria-haspopup="true" aria-expanded="false" data-dropdown-event="click" data-dropdown-target="#profileMenu" data-dropdown-type="css-animation" data-dropdown-duration="300"
              data-dropdown-animation-in="fadeIn" data-dropdown-animation-out="fadeOut">
                <span class="g-pos-rel">
        <span class="u-badge-v2--xs u-badge--top-right g-hidden-sm-up g-bg-secondary g-mr-5"></span>
                <img class="g-width-30 g-width-40--md g-height-30 g-height-40--md rounded-circle g-mr-10--sm" src="../assets/img-temp/130x130/img1.jpg" alt="Image description">
                </span>
                <span class="g-pos-rel g-top-2">
        <span class="g-hidden-sm-down"><?php echo $_SESSION['fullname'];?></span>
                <i class="hs-admin-angle-down g-pos-rel g-top-2 g-ml-10"></i>
                </span>
              </a>

              <!-- Top User Menu -->
              <ul id="profileMenu" class="g-pos-abs g-left-0 g-width-100x--lg g-nowrap g-font-size-14 g-py-20 g-mt-17 rounded" aria-labelledby="profileMenuInvoker">
                
                <li class="g-mb-10">
                  <a class="media g-color-primary--hover g-py-5 g-px-20" href="#!">
                    <span class="d-flex align-self-center g-mr-12">
          <i class="hs-admin-user"></i>
        </span>
                    <span class="media-body align-self-center">My Profile</span>
                  </a>
                </li>
                <li class="g-mb-10">
                  <a class="media g-color-primary--hover g-py-5 g-px-20" href="#!">
                    <span class="d-flex align-self-center g-mr-12">
          <i class="hs-admin-rocket"></i>
        </span>
                    <span class="media-body align-self-center">License</span>
                  </a>
                </li>
                
                <li class="g-mb-10">
                  <a class="media g-color-primary--hover g-py-5 g-px-20" href="#!">
                    <span class="d-flex align-self-center g-mr-12">
          <i class="hs-admin-headphone-alt"></i>
        </span>
                    <span class="media-body align-self-center">Get Support</span>
                  </a>
                </li>
                <li class="mb-0">
                  <a class="media g-color-primary--hover g-py-5 g-px-20" href="../logout.php">
                    <span class="d-flex align-self-center g-mr-12">
          <i class="hs-admin-shift-right"></i>
        </span>
                    <span class="media-body align-self-center">Sign Out</span>
                  </a>
                </li>
              </ul>
              <!-- End Top User Menu -->
            </div>
          </div>
          <!-- End Top User -->
        </div>
        <!-- End Messages/Notifications/Top Search Bar/Top User -->

      </nav>

    </div>
  </header>
  <!-- End Header -->


  <main class="container-fluid px-0 g-pt-65">
    <div class="row no-gutters g-pos-rel g-overflow-hidden">
      <!-- Sidebar Nav -->
      <div id="sideNav" class="col-auto u-sidebar-navigation-v1 u-sidebar-navigation--dark">
        <ul id="sideNavMenu" class="u-sidebar-navigation-v1-menu u-side-nav--top-level-menu g-min-height-100vh mb-0">
          <!-- Dashboards -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--top-level-menu-item u-side-nav-opened has-active">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="#!" data-hssm-target="#subMenu1">
              <span class="d-flex align-self-center g-pos-rel g-font-size-18 g-mr-18">
      <i class="hs-admin-server"></i>
    </span>
              <span class="media-body align-self-center">Dashboards</span>
              <span class="d-flex align-self-center u-side-nav--control-icon">
      <i class="hs-admin-angle-right"></i>
    </span>
              <span class="u-side-nav--has-sub-menu__indicator"></span>
            </a>

            <!-- Dashboards: Submenu-1 -->
            <ul id="subMenu1" class="u-sidebar-navigation-v1-menu u-side-nav--second-level-menu mb-0" style="display: block;">
              <!-- Dashboards v1 -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../dashboards/dashboard-v1.php">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-infinite"></i>
        </span>
                  <span class="media-body align-self-center">Dashboards v1</span>
                </a>
              </li>
              <!-- End Dashboards v1 -->

              <!-- Dashboards v2 -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../dashboards/dashboard-v2.php">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-blackboard"></i>
        </span>
                  <span class="media-body align-self-center">Dashboards v2</span>
                </a>
              </li>
              <!-- End Dashboards v2 -->

              <!-- Dashboards v3 -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12 active" href="../dashboards/dashboard-v3.php">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-harddrive"></i>
        </span>
                  <span class="media-body align-self-center">Dashboards v3</span>
                </a>
              </li>
              <!-- End Dashboards v3 -->

              <!-- Dashboards v4 -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../dashboards/dashboard-v4.php">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-harddrive"></i>
        </span>
                  <span class="media-body align-self-center">Dashboards v4</span>
                </a>
              </li>
              <!-- End Dashboards v4 -->
            </ul>
            <!-- End Dashboards: Submenu-1 -->
          </li>
          <!-- End Dashboards -->

          <!-- Layouts Settings -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--top-level-menu-item">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="#!" data-hssm-target="#subMenu2">
              <span class="d-flex align-self-center g-pos-rel g-font-size-18 g-mr-18">
      <i class="hs-admin-settings"></i>
    </span>
              <span class="media-body align-self-center">Layouts Settings</span>
              <span class="d-flex align-self-center u-side-nav--control-icon">
      <i class="hs-admin-angle-right"></i>
    </span>
              <span class="u-side-nav--has-sub-menu__indicator"></span>
            </a>

            <!-- Layouts Settings: Submenu-1 -->
            <ul id="subMenu2" class="u-sidebar-navigation-v1-menu u-side-nav--second-level-menu mb-0">
              <!-- Header Static -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../layout-settings/header-static.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-media-center-alt"></i>
        </span>
                  <span class="media-body align-self-center">Header Static</span>
                </a>
              </li>
              <!-- End Header Static -->

              <!-- Hide Sidebar -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../layout-settings/sidebar-hide.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-sidebar-none"></i>
        </span>
                  <span class="media-body align-self-center">Hide Sidebar</span>
                </a>
              </li>
              <!-- End Hide Sidebar -->

              <!-- Light Layout -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../layout-settings/layout-light.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-media-left-alt"></i>
        </span>
                  <span class="media-body align-self-center">Light Layout</span>
                </a>
              </li>
              <!-- End Light Layout -->

              <!-- Dark Layout: body v.2 -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../layout-settings/layout-dark-body-v2.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-media-center-alt"></i>
        </span>
                  <span class="media-body align-self-center">Dark Layout: body v.2</span>
                </a>
              </li>
              <!-- End Dark Layout: body v.2 -->

              <!-- Light Layout: body v.2 -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../layout-settings/layout-light-body-v2.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-media-center-alt"></i>
        </span>
                  <span class="media-body align-self-center">Light Layout: body v.2</span>
                </a>
              </li>
              <!-- End Light Layout: body v.2 -->
            </ul>
            <!-- End Layouts Settings: Submenu-1 -->
          </li>
          <!-- End Layouts Settings -->

          <!-- App Views -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--top-level-menu-item">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="#!" data-hssm-target="#subMenu4">
              <span class="d-flex align-self-center g-pos-rel g-font-size-18 g-mr-18">
      <i class="hs-admin-layers"></i>
    </span>
              <span class="media-body align-self-center">App Views</span>
              <span class="d-flex align-self-center u-side-nav--control-icon">
      <i class="hs-admin-angle-right"></i>
    </span>

              <span class="u-side-nav--has-sub-menu__indicator"></span>
            </a>

            <!-- App Views: Submenu-1 -->
            <ul id="subMenu4" class="u-sidebar-navigation-v1-menu u-side-nav--second-level-menu mb-0">
              <!-- Profile Pages -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="#!" data-hssm-target="#subMenu4Profiles">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-list"></i>
        </span>
                  <span class="media-body align-self-center">Profile Pages</span>
                  <span class="d-flex align-self-center u-side-nav--control-icon">
          <i class="hs-admin-angle-right"></i>
        </span>
                </a>

                <!-- Menu Leveles: Submenu-2 -->
                <ul id="subMenu4Profiles" class="u-side-nav--third-level-menu">
                  <!-- Main -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../app-views/app-profile.html">Profile Information</a>
                  </li>
                  <!-- End Main -->

                  <!-- Biography -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../app-views/app-profile-biography.html">Biography</a>
                  </li>
                  <!-- End Biography -->

                  <!-- Interests -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../app-views/app-profile-interests.html">Interests</a>
                  </li>
                  <!-- End Interests -->

                  <!-- Mobile -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../app-views/app-profile-mobile.html">Mobile</a>
                  </li>
                  <!-- End Mobile -->

                  <!-- Photos & Videos -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../app-views/app-profile-photos-and-videos.html">Photos &amp; Videos</a>
                  </li>
                  <!-- End Photos & Videos -->

                  <!-- Payment Methods -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../app-views/app-profile-payment-methods.html">Payment Methods</a>
                  </li>
                  <!-- End Payment Methods -->

                  <!-- Transactions -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../app-views/app-profile-transactions.html">Transactions</a>
                  </li>
                  <!-- End Transactions -->

                  <!-- Security -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../app-views/app-profile-security.html">Security</a>
                  </li>
                  <!-- End Security -->

                  <!-- Upgrade My Plan -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../app-views/app-profile-upgrade-plan.html">Upgrade My Plan</a>
                  </li>
                  <!-- End Upgrade My Plan -->

                  <!-- Invited Friends -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../app-views/app-profile-invite.html">Invited Friends</a>
                  </li>
                  <!-- End Invited Friends -->

                  <!-- Connected Accounts -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../app-views/app-profile-connected-accounts.html">Connected Accounts</a>
                  </li>
                  <!-- End Connected Accounts -->
                </ul>
                <!-- End Menu Leveles: Submenu-2 -->
              </li>
              <!-- End Profile Pages -->

              <!-- Projects -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../app-views/app-projects.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-media-left"></i>
        </span>
                  <span class="media-body align-self-center">Projects</span>
                </a>
              </li>
              <!-- End Projects -->

              <!-- Chat -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../app-views/app-chat.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-comments"></i>
        </span>
                  <span class="media-body align-self-center">Chat</span>
                </a>
              </li>
              <!-- End Chat -->

              <!-- File Manager -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../app-views/app-file-manager.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-folder"></i>
        </span>
                  <span class="media-body align-self-center">File Manager</span>
                  <span class="d-flex align-self-center">
          <span class="d-inline-block text-center g-min-width-35 g-bg-primary g-font-size-12 g-color-white g-rounded-15 g-px-8 g-py-1">10</span>
                  </span>
                </a>
              </li>
              <!-- End File Manager -->

              <!-- User Contacts -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../app-views/app-contacts.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-id-badge"></i>
        </span>
                  <span class="media-body align-self-center">User Contacts</span>
                </a>
              </li>
              <!-- End User Contacts -->
            </ul>
            <!-- End App Views: Submenu-1 -->
          </li>
          <!-- End App Views -->

          <!-- Forms -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--top-level-menu-item">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="#!" data-hssm-target="#subMenu7">
              <span class="d-flex align-self-center g-pos-rel g-font-size-18 g-mr-18">
      <i class="hs-admin-pencil-alt"></i>
    </span>
              <span class="media-body align-self-center">Forms</span>
              <span class="d-flex align-self-center u-side-nav--control-icon">
      <i class="hs-admin-angle-right"></i>
    </span>

              <span class="u-side-nav--has-sub-menu__indicator"></span>
            </a>

            <!-- Forms: Submenu-1 -->
            <ul id="subMenu7" class="u-sidebar-navigation-v1-menu u-side-nav--second-level-menu mb-0">
              <!-- Elements -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="#!" data-hssm-target="#subMenu7Elements">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-list"></i>
        </span>
                  <span class="media-body align-self-center">Elements</span>
                  <span class="d-flex align-self-center u-side-nav--control-icon">
          <i class="hs-admin-angle-right"></i>
        </span>
                </a>

                <!-- Menu Leveles: Submenu-2 -->
                <ul id="subMenu7Elements" class="u-side-nav--third-level-menu">
                  <!-- Text Inputs -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-text-inputs.html">Text Inputs</a>
                  </li>
                  <!-- End Text Inputs -->

                  <!-- Textareas -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-textareas.html">Textareas</a>
                  </li>
                  <!-- End Textareas -->

                  <!-- Text Editors -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-text-editors.html">Text Editors</a>
                  </li>
                  <!-- End Text Editors -->

                  <!-- Selects -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-selects.html">Selects</a>
                  </li>
                  <!-- End Selects -->

                  <!-- Advanced Selects -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-advanced-selects.html">Advanced Selects</a>
                  </li>
                  <!-- End Advanced Selects -->

                  <!-- Checkboxes &amp; Radios -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-checkboxes-radios.html">Checkboxes &amp; Radios</a>
                  </li>
                  <!-- End Checkboxes &amp; Radios -->

                  <!-- Toggles -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-toggles.html">Toggles</a>
                  </li>
                  <!-- End Toggles -->

                  <!-- File Inputs -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-file-inputs.html">File Inputs</a>
                  </li>
                  <!-- End File Inputs -->

                  <!-- Sliders -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-sliders.html">Sliders</a>
                  </li>
                  <!-- End Sliders -->

                  <!-- Text Inputs with Tags -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-tags.html">Text Inputs with Tags</a>
                  </li>
                  <!-- End Text Inputs with Tags -->

                  <!-- Ratings -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-ratings.html">Ratings</a>
                  </li>
                  <!-- End Ratings -->

                  <!-- Datepickers -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-datepickers.html">Datepickers</a>
                  </li>
                  <!-- End Datepickers -->

                  <!-- Quantities -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-quantities.html">Quantities</a>
                  </li>
                  <!-- End Quantities -->

                  <!-- Slider Controls -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-elemets-slider-controls.html">Slider Controls</a>
                  </li>
                  <!-- End Slider Controls -->
                </ul>
                <!-- End Menu Leveles: Submenu-2 -->
              </li>
              <!-- End Elements -->

              <!-- Layouts -->
              <!--     <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
      <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="#!">
        <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-media-overlay"></i>
        </span>
        <span class="media-body align-self-center">Layouts</span>
      </a>
    </li> -->
              <!-- End Layouts -->

              <!-- Validation -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="#!" data-hssm-target="#subMenu7Validation">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-list"></i>
        </span>
                  <span class="media-body align-self-center">Validation</span>
                  <span class="d-flex align-self-center u-side-nav--control-icon">
          <i class="hs-admin-angle-right"></i>
        </span>
                </a>

                <!-- Validation: Submneu -->
                <ul id="subMenu7Validation" class="u-side-nav--third-level-menu">
                  <!-- States -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="../forms/forms-validation-states.html">States</a>
                  </li>
                  <!-- End States -->
                </ul>
                <!-- Validation: Submneu -->
              </li>
              <!-- End Validation -->
            </ul>
            <!-- End Forms: Submenu-1 -->
          </li>
          <!-- End Forms -->

          <!-- Tables -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--top-level-menu-item">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="#!" data-hssm-target="#subMenu8">
              <span class="d-flex align-self-center g-pos-rel g-font-size-18 g-mr-18">
      <i class="hs-admin-layout-grid-3"></i>
    </span>
              <span class="media-body align-self-center">Tables</span>
              <span class="d-flex align-self-center u-side-nav--control-icon">
      <i class="hs-admin-angle-right"></i>
    </span>

              <span class="u-side-nav--has-sub-menu__indicator"></span>
            </a>

            <!-- Tables: Submenu-1 -->
            <ul id="subMenu8" class="u-sidebar-navigation-v1-menu u-side-nav--second-level-menu mb-0">
              <!-- Basic Tables -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../tables/tables-basic.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-list-thumb"></i>
        </span>
                  <span class="media-body align-self-center">Basic Tables</span>
                </a>
              </li>
              <!-- End Basic Tables -->

              <!-- Table Designs -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../tables/tables-complex.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-media-overlay-alt-2"></i>
        </span>
                  <span class="media-body align-self-center">Complex Tables</span>
                </a>
              </li>
              <!-- End Table Designs -->

              <!-- Table Modern -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../tables/tables-modern.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-media-overlay-alt-2"></i>
        </span>
                  <span class="media-body align-self-center">Modern Tables</span>
                </a>
              </li>
              <!-- End Table Modern -->
            </ul>
            <!-- End Tables: Submenu-1 -->
          </li>
          <!-- End Tables -->

          <!-- Panels/Cards -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--top-level-menu-item">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="#!" data-hssm-target="#subMenu6">
              <span class="d-flex align-self-center g-pos-rel g-font-size-18 g-mr-18">
      <i class="hs-admin-layout-media-center-alt"></i>
    </span>
              <span class="media-body align-self-center">Panels/Cards</span>
              <span class="d-flex align-self-center u-side-nav--control-icon">
      <i class="hs-admin-angle-right"></i>
    </span>

              <span class="u-side-nav--has-sub-menu__indicator"></span>
            </a>

            <!-- Panels/Cards: Submenu-1 -->
            <ul id="subMenu6" class="u-sidebar-navigation-v1-menu u-side-nav--second-level-menu mb-0">
              <!-- Panel Variations -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../panels/panel-variations.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-cta-btn-left"></i>
        </span>
                  <span class="media-body align-self-center">Panel Variations</span>
                </a>
              </li>
              <!-- End Panel Variations -->

              <!-- Panel with Tabs -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../panels/panel-options.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-cta-right"></i>
        </span>
                  <span class="media-body align-self-center">Panel's Options</span>
                </a>
              </li>
              <!-- End Panel with Tabs -->

              <!-- Panel Options
    <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
      <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../panels/panel-options.html">
        <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-cta-center"></i>
        </span>
        <span class="media-body align-self-center">Panel Options</span>
      </a>
    </li>
    End Panel Options -->
            </ul>
            <!-- End Panels/Cards: Submenu-1 -->
          </li>
          <!-- End Panels/Cards -->

          <!-- Notifications -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--top-level-menu-item">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="#!" data-hssm-target="#subMenu9">
              <span class="d-flex align-self-center g-pos-rel g-font-size-18 g-mr-18">
      <i class="hs-admin-layout-list-thumb"></i>
    </span>
              <span class="media-body align-self-center">Notifications</span>
              <span class="d-flex align-self-center u-side-nav--control-icon">
      <i class="hs-admin-angle-right"></i>
    </span>

              <span class="u-side-nav--has-sub-menu__indicator"></span>
            </a>

            <!-- Notifications: Submenu-1 -->
            <ul id="subMenu9" class="u-sidebar-navigation-v1-menu u-side-nav--second-level-menu mb-0">
              <!-- Colorful Notifications -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../notifications/notifications-colorful.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-cta-btn-right"></i>
        </span>
                  <span class="media-body align-self-center">Colorful Notifications</span>
                </a>
              </li>
              <!-- End Colorful Notifications -->

              <!-- Light Notifications -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../notifications/notifications-light.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-cta-btn-left"></i>
        </span>
                  <span class="media-body align-self-center">Light Notifications</span>
                </a>
              </li>
              <!-- End Light Notifications -->

              <!-- Dark Notifications -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../notifications/notifications-dark.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-layout-cta-center"></i>
        </span>
                  <span class="media-body align-self-center">Dark Notifications</span>
                </a>
              </li>
              <!-- End Dark Notifications -->

              <!-- Notifications Builder -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../notifications/notifications-builder.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-infinite"></i>
        </span>
                  <span class="media-body align-self-center">Notifications Builder</span>
                </a>
              </li>
              <!-- End Notifications Builder -->
            </ul>
            <!-- Notifications: Submenu-1 -->
          </li>
          <!-- End Notifications -->

          <!-- Metrics -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--top-level-menu-item">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="../metrics/metrics.html">
              <span class="d-flex align-self-center g-pos-rel g-font-size-18 g-mr-18">
      <i class="hs-admin-pie-chart"></i>
    </span>
              <span class="media-body align-self-center">Metrics</span>
            </a>
          </li>
          <!-- End Metrics -->

          <!-- UI Components -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--top-level-menu-item">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="#!" data-hssm-target="#subMenu5">
              <span class="d-flex align-self-center g-pos-rel g-font-size-18 g-mr-18">
      <i class="hs-admin-infinite"></i>
    </span>
              <span class="media-body align-self-center">UI Components</span>
              <span class="d-flex align-self-center u-side-nav--control-icon">
      <i class="hs-admin-angle-right"></i>
    </span>
              <span class="u-side-nav--has-sub-menu__indicator"></span>
            </a>

            <!-- UI Components: Submenu -->
            <ul id="subMenu5" class="u-sidebar-navigation-v1-menu u-side-nav--second-level-menu mb-0">
              <!-- Icons -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../ui-components/ui-icons.html">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-wand"></i>
        </span>
                  <span class="media-body align-self-center">Icons</span>
                </a>
              </li>
              <!-- End Icons -->

              <!-- Buttons -->
              <!--     <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
      <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="../ui-components/ui-buttons.html">
        <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-view-grid"></i>
        </span>
        <span class="media-body align-self-center">Buttons</span>
      </a>
    </li> -->
              <!-- End Buttons -->
            </ul>
            <!-- End UI Components: Submenu -->
          </li>
          <!-- End UI Components -->

          <!-- Timeline History -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--top-level-menu-item">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="../pages/pages-timeline.html">
              <span class="d-flex align-self-center g-pos-rel g-font-size-18 g-mr-18">
      <span class="u-badge-v2--xs u-badge--bottom-right g-bg-secondary"></span>
              <i class="hs-admin-timer"></i>
              </span>
              <span class="media-body align-self-center">Timeline History</span>
              <span class="d-flex align-self-center">
      <span class="d-inline-block text-center g-min-width-35 g-bg-secondary g-font-size-12 g-color-white g-rounded-15 g-px-8 g-py-1">5</span>
              </span>
            </a>
          </li>
          <!-- End Timeline History -->

          <!-- Menu Leveles -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--top-level-menu-item">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="#!" data-hssm-target="#subMenuLevels1">
              <span class="d-flex align-self-center g-pos-rel g-font-size-18 g-mr-18">
      <i class="hs-admin-list-ol"></i>
    </span>
              <span class="media-body align-self-center">Menu Levels</span>
              <span class="d-flex align-self-center u-side-nav--control-icon">
      <i class="hs-admin-angle-right"></i>
    </span>
              <span class="u-side-nav--has-sub-menu__indicator"></span>
            </a>

            <!-- Menu Leveles: Submenu-1 -->
            <ul id="subMenuLevels1" class="u-sidebar-navigation-v1-menu u-side-nav--second-level-menu mb-0">
              <!-- Sub Level 2 -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="#!">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-list"></i>
        </span>
                  <span class="media-body align-self-center">Sub Level 2</span>
                </a>
              </li>
              <!-- End Sub Level 2 -->

              <!-- Sub Level 2 -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--has-sub-menu u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="#!" data-hssm-target="#subMenuLevels2">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-list"></i>
        </span>
                  <span class="media-body align-self-center">Sub Level 2</span>
                  <span class="d-flex align-self-center u-side-nav--control-icon">
          <i class="hs-admin-angle-right"></i>
        </span>
                </a>

                <!-- Menu Leveles: Submenu-2 -->
                <ul id="subMenuLevels2" class="u-side-nav--third-level-menu">
                  <!-- Sub Level 3 -->
                  <li class="u-side-nav--third-level-menu-item u-side-nav--has-sub-menu">
                    <a class="media d-flex u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="#!" data-hssm-target="#subMenuLevels3">
                      <span class="media-body align-self-center">Sub Level 3</span>
                      <span class="d-flex align-self-center u-side-nav--control-icon">
              <i class="hs-admin-angle-right"></i>
            </span>
                    </a>

                    <!-- Menu Leveles: Submenu-3 -->
                    <ul id="subMenuLevels3" class="u-side-nav--fourth-level-menu">
                      <!-- Sub Level 4 -->
                      <li class="u-side-nav--fourth-level-menu-item">
                        <a class="u-side-nav--fourth-level-menu-link g-px-15 g-py-6" href="#!">
                          <span class="media-body align-self-center">Sub Level 4</span>
                        </a>
                      </li>
                      <!-- End Sub Level 4 -->

                      <!-- Sub Level 4 -->
                      <li class="u-side-nav--fourth-level-menu-item">
                        <a class="u-side-nav--fourth-level-menu-link g-px-15 g-py-6" href="#!">
                          <span class="media-body align-self-center">Sub Level 4</span>
                        </a>
                      </li>
                      <!-- End Sub Level 4 -->

                      <!-- Sub Level 4 -->
                      <li class="u-side-nav--fourth-level-menu-item">
                        <a class="u-side-nav--fourth-level-menu-link g-px-15 g-py-6" href="#!">
                          <span class="media-body align-self-center">Sub Level 4</span>
                        </a>
                      </li>
                      <!-- End Sub Level 4 -->
                    </ul>
                    <!-- End Menu Leveles: Submenu-3 -->
                  </li>
                  <!-- End Sub Level 3 -->

                  <!-- Sub Level 3 -->
                  <li class="u-side-nav--third-level-menu-item">
                    <a class="u-side-nav--third-level-menu-link u-side-nav--hide-on-hidden g-pl-8 g-pr-15 g-py-6" href="#!">Sub Level 3</a>
                  </li>
                  <!-- End Sub Level 3 -->
                </ul>
                <!-- End Menu Leveles: Submenu-2 -->
              </li>
              <!-- End Sub Level 2 -->

              <!-- Sub Level 2 -->
              <li class="u-sidebar-navigation-v1-menu-item u-side-nav--second-level-menu-item">
                <a class="media u-side-nav--second-level-menu-link g-px-15 g-py-12" href="#!">
                  <span class="d-flex align-self-center g-mr-15 g-mt-minus-1">
          <i class="hs-admin-list"></i>
        </span>
                  <span class="media-body align-self-center">Sub Level 2</span>
                </a>
              </li>
              <!-- End Sub Level 2 -->
            </ul>
            <!-- End Menu Leveles: Submenu-1 -->
          </li>
          <!-- End Menu Leveles -->

          <!-- Packages -->
          <li class="u-sidebar-navigation-v1-menu-item u-side-nav--top-level-menu-item">
            <a class="media u-side-nav--top-level-menu-link u-side-nav--hide-on-hidden g-px-15 g-py-12" href="../packages.html">
              <span class="d-flex align-self-center g-font-size-18 g-mr-18">
      <i class="hs-admin-medall"></i>
    </span>
              <span class="media-body align-self-center">Packages</span>
            </a>
          </li>
          <!-- End Packages -->

        </ul>
      </div>
      <!-- End Sidebar Nav -->


      <div class="col g-ml-45 g-ml-0--lg g-pb-65--md">
        <div class="g-pa-20">
          <div class="row">
            <div class="col-sm-6 col-lg-6 col-xl-4 g-mb-30">
              <!-- Project Card -->
              <div class="card g-brd-gray-light-v7 h-100 text-center g-pa-15 g-pa-25-30--md">
                <header class="media g-mb-40">
                  <h3 class="d-flex align-self-center text-uppercase g-font-size-12 g-font-size-default--md g-color-black mb-0">Hình thức kết nối</h3>
                </header>

                <section class="g-mb-40">
                  <div class="js-donut-chart g-pos-rel mx-auto" style="width: 128px; height: 128px;" data-series='[
                    <?php echo $imgvid . "," . $local_account . "," . $sms . "," . $social . "," . $survey . "," . $voucher; ?>]' data-border-width="10" data-start-angle="0" data-fill-colors='["#3dd1e8","#1d75e5","#e62154","#ffff00","#008000","#000000"]'>
                    <i class="hs-admin-clipboard g-absolute-centered g-font-size-40 g-color-gray-light-v3"></i>
                  </div>
                </section>

                <section class="row">
                  
                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-lightblue-v3 g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $imgvid; ?>
                        <span class="g-font-size-default g-valign-top">%</span>
                      </strong>
                      <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">Hình ảnh/Video</span>
                    </div>
                  </div>
                  

                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-darkblue-v2 g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $local_account; ?>
                        <span class="g-font-size-default g-valign-top">%</span>
                      </strong>
                      <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">Tài khoản</span>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-primary g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $sms; ?>
                        <span class="g-font-size-default g-valign-top">%</span>
                      </strong>
                      <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">Tin nhắn</span>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-yellow g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $social; ?>
                        <span class="g-font-size-default g-valign-top">%</span>
                      </strong>
                      <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">Mạng xã hội</span>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-green g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $survey; ?>
                        <span class="g-font-size-default g-valign-top">%</span>
                      </strong>
                      <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">Khảo sát</span>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="g-brd-top g-brd-2 g-brd-black g-pt-18">
                      <strong class="d-block g-line-height-1 g-font-weight-500 g-font-size-18 g-color-black g-mb-8--sm"> <?php echo $voucher; ?>
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
                  <h3 class="d-flex align-self-center text-uppercase g-font-size-12 g-font-size-default--md g-color-black mb-0">Tổng số kết nối</h3>

                  <div class="media-body d-flex justify-content-end">Tháng <?php echo $current_month . "/" . $current_year; ?></div>
                </header>

                <div class="g-pa-15 g-pa-0-30-25--md">
                  <section class="media g-mb-20">
                    <div class="media-body align-self-end g-line-height-1_3 g-font-weight-300 g-font-size-40 g-color-black">
                      <?php echo $total_connections;?>
                      <span class="g-font-size-16 g-color-gray-dark-v7"><?php 
                        if ($delta_total_connections >= 0) {
                          echo "+" . $delta_total_connections;
                        } else {
                          echo "-" . abs($delta_total_connections);
                        }
                        ?></span>
                      <h5 class="g-font-weight-300 g-font-size-default g-font-size-16--md g-color-gray-dark-v6 mb-0">Kết nối</h5>
                    </div>

                    <div class="d-flex align-self-end ml-auto">
                      <div class="d-block text-right g-font-size-16">
                        <span class="d-block g-color-black">Ngày kết nối nhiều nhất</span>
                        <span class="d-block g-color-lightblue-v3"><?php echo date("d-m-Y",strtotime($top_connection_day)) . "<br>" . $number_top_connection_day;?></span>
                      </div>
                    </div>
                  </section>

                  <section class="media g-mb-20">
                    <div class="media-body align-self-end g-line-height-1_3 g-font-weight-300 g-font-size-40 g-color-black">
                      <?php echo $total_clients;?>
                      <span class="g-font-size-16 g-color-gray-dark-v7"><?php 
                        if ($delta_total_clients >= 0) {
                          echo "+" . $delta_total_clients;
                        } else {
                          echo "-" . abs($delta_total_clients);
                        }
                        ?></span>
                      <h5 class="g-font-weight-300 g-font-size-default g-font-size-16--md g-color-gray-dark-v6 mb-0">Người dùng</h5>
                    </div>

                    <div class="d-flex align-self-end ml-auto">
                      <div class="d-block text-right g-font-size-16">
                        <span class="d-block g-color-black">Giờ kết nối nhiều nhất</span>
                        <span class="d-block g-color-lightblue-v3"><?php echo $max_hour . ":00<br>" . $max_connection_hour ?></span>
                      </div>
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
                    <h4 class="d-flex align-self-center text-uppercase g-font-size-12 g-font-size-default--md g-color-black mb-0">Thống kê người dùng</h4>
                  </span>
                  </header>

                  <div class="d-flex align-items-center g-mb-25">
                    
                  </div>
                  <div class="js-area-chart u-area-chart--v1 g-pos-rel g-line-height-0" data-height="300px" data-mobile-height="180px" data-high="<?php echo $max_clients; ?>" data-low="0" data-offset-x="30" data-offset-y="0" data-postfix=" " data-is-show-area="true" data-is-show-line="true"
                  data-is-show-point="true" data-is-full-width="true" data-is-stack-bars="true" data-is-show-axis-x="false" data-is-show-axis-y="false" data-is-show-tooltips="true" data-tooltip-description-position="right" data-tooltip-custom-class="u-tooltip--v2 g-font-weight-300 g-font-size-default g-color-gray-dark-v6"
                  data-align-text-axis-x="left" data-fill-opacity=".2" data-fill-colors='["#e62154"]' data-stroke-color="#e62154" data-stroke-dash-array="0" data-text-size-x="14px" data-text-color-x="#000000" data-text-offset-top-x="10"
                  data-text-size-y="14px" data-text-color-y="#53585e" data-points-colors='["#e62154"]' data-series='[
                    [
                    <?php
                        $temp_string = '';                        
                        foreach($client_series as $value)
                            $temp_string .= $value .',';
                        echo chop($temp_string,",");
                    ?>
                    ]
                   ]' data-labels='[
                    <?php
                        $temp_string = '';
                        foreach($client_labels as $labels)
                             $temp_string .= '"'. $labels . '",';
                        echo chop($temp_string,",");
                    ?>
                   ]' data-is-show-axis-x="false" data-is-show-axis-y="false" data-height="180px" data-high="<?php echo $max_clients;?>"></div>
                   
                  </div>
              </div>
              <!-- End Panel -->
            </div>

            <!-- Statistic Card -->
            <div class="col-xl-8">
              <!-- Statistic Card -->
              <div class="card g-brd-gray-light-v7 g-pa-15 g-pa-25-30--md g-mb-30">
                <header class="media g-mb-30">
                  <h3 class="d-flex align-self-center text-uppercase g-font-size-12 g-font-size-default--md g-color-black mb-0">Thống kê kết nối</h3>
                  <div class="media-body d-flex justify-content-end">
                    Tháng <?php echo $current_month . "/" . $current_year; ?>
                  </div>
                  <!--
                  <div class="media-body d-flex justify-content-end">
                    <div id="rangePickerWrapper2" class="d-flex align-items-center u-datepicker-right u-datepicker--v3 g-pr-10">
                      <input id="rangeDatepicker2" class="g-font-size-12 g-font-size-default--md" type="text" data-rp-wrapper="#rangePickerWrapper2" data-rp-type="range" data-rp-date-format="d M Y" data-rp-default-date='["01 Jan 2016", "31 Dec 2017"]'>
                      <i class="hs-admin-angle-down g-absolute-centered--y g-right-0 g-color-gray-light-v3"></i>
                    </div>

                    <a class="d-flex align-items-center hs-admin-panel u-link-v5 g-font-size-20 g-color-gray-light-v3 g-color-secondary--hover g-ml-5 g-ml-30--md" href="#!"></a>
                  </div>
                -->
                </header>

                <section>
                  

                  <div class="js-area-chart u-area-chart--v1 g-pos-rel g-line-height-0" data-height="300px" data-mobile-height="180px" data-high="<?php echo $max_connections; ?>" data-low="0" data-offset-x="30" data-offset-y="0" data-postfix=" " data-is-show-area="true" data-is-show-line="true"
                  data-is-show-point="true" data-is-full-width="true" data-is-stack-bars="true" data-is-show-axis-x="false" data-is-show-axis-y="false" data-is-show-tooltips="true" data-tooltip-description-position="right" data-tooltip-custom-class="u-tooltip--v2 g-font-weight-300 g-font-size-default g-color-gray-dark-v6"
                  data-align-text-axis-x="left" data-fill-opacity=".2" data-fill-colors='["#1d75e5"]' data-stroke-color="#1d75e5" data-stroke-dash-array="0" data-text-size-x="14px" data-text-color-x="#000000" data-text-offset-top-x="10"
                  data-text-size-y="14px" data-text-color-y="#53585e" data-points-colors='["#1d75e5"]' data-series='[
              [
                <?php
                        $temp_string = '';                        
                        foreach($connection_series as $value)
                            $temp_string .= '{"meta": "", "value": ' . $value .'},';
                        echo chop($temp_string,",");
                    ?>
              ]
            ]' data-labels='[<?php
                        $temp_string = '';
                        foreach($connection_labels as $labels)
                             $temp_string .= '"'. $labels . '",';
                        echo chop($temp_string,",");
                    ?>]'></div>
                </section>
              </div>
              <!-- End Statistic Card -->
            </div>
            <!-- End Statistic Card -->

            <!-- Messages -->
            <div class="col-xl-4">
              <!-- Messages Cards -->
              <div class="card g-brd-gray-light-v7 g-mb-30">
                <header class="media g-pa-15 g-pa-25-30-0--md g-mb-20">
                  <h3 class="d-flex align-self-center text-uppercase g-font-size-12 g-font-size-default--md g-color-black mb-0">Thống kê thiết bị đầu cuối</h3>
                </header>

                <div class="js-custom-scroll g-height-400 g-pa-15 g-pa-0-30-25--md">
                  <section class="media">                    

                    <div class="media-body g-color-gray-dark-v6">
                      <div class="media g-mb-12">
                        <h3 class="d-flex align-self-center g-font-weight-300 g-font-size-16 mb-0">Thiết bị sử dụng nhiều </h3>
                        <em class="d-flex align-self-center align-items-center g-font-style-normal g-color-gray-dark-v6 ml-auto">
            
            <span class="g-font-weight-300 g-font-size-12 g-font-size-default--md g-ml-4 g-ml-8--md"><?php echo $top_device; ?> </span>
          </em>
                      </div>

                      <p class="g-font-weight-300 g-font-size-default mb-0"><?php echo $number_top_device; ?></p>
                    </div>
                  </section>

                  <hr class="d-flex g-brd-gray-light-v4 my-25">

                  <section class="media">                    

                    <div class="media-body g-color-gray-dark-v6">
                      <div class="media g-mb-12">
                        <h3 class="d-flex align-self-center g-font-weight-300 g-font-size-16 mb-0">Hệ điều hành sử dụng nhiều </h3>
                        <em class="d-flex align-self-center align-items-center g-font-style-normal g-color-gray-dark-v6 ml-auto">
            
            <span class="g-font-weight-300 g-font-size-12 g-font-size-default--md g-ml-4 g-ml-8--md"><?php echo $top_os; ?> </span>
          </em>
                      </div>

                      <p class="g-font-weight-300 g-font-size-default mb-0"><?php echo $number_top_os; ?></p>
                    </div>
                  </section>

                  <hr class="d-flex g-brd-gray-light-v4 my-25">

                  <section class="media">                    

                    <div class="media-body g-color-gray-dark-v6">
                      <div class="media g-mb-12">
                        <h3 class="d-flex align-self-center g-font-weight-300 g-font-size-16 mb-0">Trình duyệt sử dụng nhiều </h3>
                        <em class="d-flex align-self-center align-items-center g-font-style-normal g-color-gray-dark-v6 ml-auto">
            
            <span class="g-font-weight-300 g-font-size-12 g-font-size-default--md g-ml-4 g-ml-8--md"><?php echo $top_browser; ?> </span>
          </em>
                      </div>

                      <p class="g-font-weight-300 g-font-size-default mb-0"><?php echo $number_top_browser; ?></p>
                    </div>
                  </section>
                </div>
              </div>
              <!-- End Messages Cards -->
            </div>
            <!-- End Messages -->

            <!-- Table -->
            <div class="col-xl-12">
              <div class="table-responsive g-mb-40">
                <table class="table u-table--v3 g-color-black">
                  <thead>
                    <tr>
                      <th class="g-px-30">
                        <div class="media">
                          <div class="d-flex align-self-center">Name</div>

                          <div class="d-flex align-self-center ml-auto">
                            <span class="d-inline-block g-width-10 g-line-height-1 g-font-size-10">
                            <a class="g-color-gray-light-v6 g-color-secondary--hover g-text-underline--none--hover" href="#!">
                              <i class="hs-admin-angle-up"></i>
                            </a>
                            <a class="g-color-gray-light-v6 g-color-secondary--hover g-text-underline--none--hover" href="#!">
                              <i class="hs-admin-angle-down"></i>
                            </a>
                          </span>
                          </div>
                        </div>
                      </th>
                      <th class="g-px-30">
                        <div class="media">
                          <div class="d-flex align-self-center">Role</div>

                          <div class="d-flex align-self-center ml-auto">
                            <span class="d-inline-block g-width-10 g-line-height-1 g-font-size-10">
                            <a class="g-color-gray-light-v6 g-color-secondary--hover g-text-underline--none--hover" href="#!">
                              <i class="hs-admin-angle-up"></i>
                            </a>
                            <a class="g-color-gray-light-v6 g-color-secondary--hover g-text-underline--none--hover" href="#!">
                              <i class="hs-admin-angle-down"></i>
                            </a>
                          </span>
                          </div>
                        </div>
                      </th>
                      <th class="g-px-30">
                        <div class="media">
                          <div class="d-flex align-self-center">Activity</div>

                          <div class="d-flex align-self-center ml-auto">
                            <span class="d-inline-block g-width-10 g-line-height-1 g-font-size-10">
                            <a class="g-color-gray-light-v6 g-color-secondary--hover g-text-underline--none--hover" href="#!">
                              <i class="hs-admin-angle-up"></i>
                            </a>
                            <a class="g-color-gray-light-v6 g-color-secondary--hover g-text-underline--none--hover" href="#!">
                              <i class="hs-admin-angle-down"></i>
                            </a>
                          </span>
                          </div>
                        </div>
                      </th>
                      <th class="g-px-30">
                        <div class="media">
                          <div class="d-flex align-self-center">Category</div>

                          <div class="d-flex align-self-center ml-auto">
                            <span class="d-inline-block g-width-10 g-line-height-1 g-font-size-10">
                            <a class="g-color-gray-light-v6 g-color-secondary--hover g-text-underline--none--hover" href="#!">
                              <i class="hs-admin-angle-up"></i>
                            </a>
                            <a class="g-color-gray-light-v6 g-color-secondary--hover g-text-underline--none--hover" href="#!">
                              <i class="hs-admin-angle-down"></i>
                            </a>
                          </span>
                          </div>
                        </div>
                      </th>
                      <th class="g-px-30"></th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr>
                      <td class="g-px-30">
                        <div class="media">
                          <div class="d-flex align-self-center">
                            <img class="g-width-36 g-height-36 rounded-circle g-mr-15" src="../../assets/img-temp/100x100/img4.jpg" alt="Image description">
                          </div>

                          <div class="media-body align-self-center text-left">Terry Ward</div>
                        </div>
                      </td>
                      <td class="g-px-30">Product Manager</td>
                      <td class="g-px-30">27 Aug 2017</td>
                      <td class="g-px-30">
                        <div class="d-inline-block">
                          <span class="d-flex align-items-center justify-content-center u-tags-v1 g-brd-around g-bg-gray-light-v8 g-bg-gray-light-v8 g-font-size-default g-color-gray-dark-v6 g-rounded-50 g-py-4 g-px-15">
                          <span class="u-badge-v2--md g-pos-stc g-transform-origin--top-left g-bg-lightblue-v3 g-mr-8"></span>
                          Employees
                          </span>
                        </div>
                      </td>
                      <td class="g-px-30">
                        <div class="g-pos-rel g-top-3 d-inline-block">
                          <a id="dropDown6Invoker" class="u-link-v5 g-line-height-0 g-font-size-24 g-color-gray-light-v6 g-color-secondary--hover" href="#!" aria-controls="dropDown6" aria-haspopup="true" aria-expanded="false" data-dropdown-event="click" data-dropdown-target="#dropDown6">
                            <i class="hs-admin-more-alt"></i>
                          </a>

                          <div id="dropDown6" class="u-shadow-v31 g-pos-abs g-right-0 g-z-index-2 g-bg-white" aria-labelledby="dropDown6Invoker">
                            <ul class="list-unstyled g-nowrap mb-0">
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-pencil g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Edit
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-archive g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Archive
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-check g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Mark as Done
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-plus g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  New Task
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-trash g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Delete
                                </a>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="g-px-30">
                        <div class="media">
                          <div class="d-flex align-self-center">
                            <div class="g-pos-rel g-width-36 g-height-36 g-bg-secondary rounded-circle g-mr-15">
                              <span class="text-uppercase g-absolute-centered g-font-size-default g-color-white">SF</span>
                            </div>
                          </div>

                          <div class="media-body align-self-center text-left">Samuel Freeman</div>
                        </div>
                      </td>
                      <td class="g-px-30">Product Manager</td>
                      <td class="g-px-30">07 Sep 2017</td>
                      <td class="g-px-30">
                        <div class="d-inline-block">
                          <span class="d-flex align-items-center justify-content-center u-tags-v1 g-brd-around g-bg-gray-light-v8 g-bg-gray-light-v8 g-font-size-default g-color-gray-dark-v6 g-rounded-50 g-py-4 g-px-15">
                          <span class="u-badge-v2--md g-pos-stc g-transform-origin--top-left g-bg-lightblue-v3 g-mr-8"></span>
                          Employees
                          </span>
                        </div>
                      </td>
                      <td class="g-px-30">
                        <div class="g-pos-rel g-top-3 d-inline-block">
                          <a id="dropDown7Invoker" class="u-link-v5 g-line-height-0 g-font-size-24 g-color-gray-light-v6 g-color-secondary--hover" href="#!" aria-controls="dropDown7" aria-haspopup="true" aria-expanded="false" data-dropdown-event="click" data-dropdown-target="#dropDown7">
                            <i class="hs-admin-more-alt"></i>
                          </a>

                          <div id="dropDown7" class="u-shadow-v31 g-pos-abs g-right-0 g-z-index-2 g-bg-white" aria-labelledby="dropDown7Invoker">
                            <ul class="list-unstyled g-nowrap mb-0">
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-pencil g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Edit
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-archive g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Archive
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-check g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Mark as Done
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-plus g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  New Task
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-trash g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Delete
                                </a>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="g-px-30">
                        <div class="media">
                          <div class="d-flex align-self-center">
                            <img class="g-width-36 g-height-36 rounded-circle g-mr-15" src="../../assets/img-temp/100x100/img5.jpg" alt="Image description">
                          </div>

                          <div class="media-body align-self-center text-left">Susie Glover</div>
                        </div>
                      </td>
                      <td class="g-px-30">Product Manager</td>
                      <td class="g-px-30">29 Feb 2017</td>
                      <td class="g-px-30">
                        <div class="d-inline-block">
                          <span class="d-flex align-items-center justify-content-center u-tags-v1 g-brd-around g-bg-gray-light-v8 g-bg-gray-light-v8 g-font-size-default g-color-gray-dark-v6 g-rounded-50 g-py-4 g-px-15">
                          <span class="u-badge-v2--md g-pos-stc g-transform-origin--top-left g-bg-lightred-v2 g-mr-8"></span>
                          Family
                          </span>
                        </div>
                      </td>
                      <td class="g-px-30">
                        <div class="g-pos-rel g-top-3 d-inline-block">
                          <a id="dropDown8Invoker" class="u-link-v5 g-line-height-0 g-font-size-24 g-color-gray-light-v6 g-color-secondary--hover" href="#!" aria-controls="dropDown8" aria-haspopup="true" aria-expanded="false" data-dropdown-event="click" data-dropdown-target="#dropDown8">
                            <i class="hs-admin-more-alt"></i>
                          </a>

                          <div id="dropDown8" class="u-shadow-v31 g-pos-abs g-right-0 g-z-index-2 g-bg-white" aria-labelledby="dropDown8Invoker">
                            <ul class="list-unstyled g-nowrap mb-0">
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-pencil g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Edit
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-archive g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Archive
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-check g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Mark as Done
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-plus g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  New Task
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-trash g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Delete
                                </a>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="g-px-30">
                        <div class="media">
                          <div class="d-flex align-self-center">
                            <div class="g-pos-rel g-width-36 g-height-36 g-bg-secondary rounded-circle g-mr-15">
                              <span class="text-uppercase g-absolute-centered g-font-size-default g-color-white">LO</span>
                            </div>
                          </div>

                          <div class="media-body align-self-center text-left">Lee Olson</div>
                        </div>
                      </td>
                      <td class="g-px-30">Product Manager</td>
                      <td class="g-px-30">27 Aug 2017</td>
                      <td class="g-px-30">
                        <div class="d-inline-block">
                          <span class="d-flex align-items-center justify-content-center u-tags-v1 g-brd-around g-bg-gray-light-v8 g-bg-gray-light-v8 g-font-size-default g-color-gray-dark-v6 g-rounded-50 g-py-4 g-px-15">
                          <span class="u-badge-v2--md g-pos-stc g-transform-origin--top-left g-bg-lightred-v2 g-mr-8"></span>
                          Family
                          </span>
                        </div>
                      </td>
                      <td class="g-px-30">
                        <div class="g-pos-rel g-top-3 d-inline-block">
                          <a id="dropDown9Invoker" class="u-link-v5 g-line-height-0 g-font-size-24 g-color-gray-light-v6 g-color-secondary--hover" href="#!" aria-controls="dropDown9" aria-haspopup="true" aria-expanded="false" data-dropdown-event="click" data-dropdown-target="#dropDown9">
                            <i class="hs-admin-more-alt"></i>
                          </a>

                          <div id="dropDown9" class="u-shadow-v31 g-pos-abs g-right-0 g-z-index-2 g-bg-white" aria-labelledby="dropDown9Invoker">
                            <ul class="list-unstyled g-nowrap mb-0">
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-pencil g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Edit
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-archive g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Archive
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-check g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Mark as Done
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-plus g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  New Task
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-trash g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Delete
                                </a>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="g-px-30">
                        <div class="media">
                          <div class="d-flex align-self-center">
                            <img class="g-width-36 g-height-36 rounded-circle g-mr-15" src="../../assets/img-temp/100x100/img6.jpg" alt="Image description">
                          </div>

                          <div class="media-body align-self-center text-left">Elizabeth Rice</div>
                        </div>
                      </td>
                      <td class="g-px-30">Product Manager</td>
                      <td class="g-px-30">23 Jan 2017</td>
                      <td class="g-px-30">
                        <div class="d-inline-block">
                          <span class="d-flex align-items-center justify-content-center u-tags-v1 g-brd-around g-bg-gray-light-v8 g-bg-gray-light-v8 g-font-size-default g-color-gray-dark-v6 g-rounded-50 g-py-4 g-px-15">
                          <span class="u-badge-v2--md g-pos-stc g-transform-origin--top-left g-bg-lightblue-v3 g-mr-8"></span>
                          Employees
                          </span>
                        </div>
                      </td>
                      <td class="g-px-30">
                        <div class="g-pos-rel g-top-3 d-inline-block">
                          <a id="dropDown10Invoker" class="u-link-v5 g-line-height-0 g-font-size-24 g-color-gray-light-v6 g-color-secondary--hover" href="#!" aria-controls="dropDown10" aria-haspopup="true" aria-expanded="false" data-dropdown-event="click" data-dropdown-target="#dropDown10">
                            <i class="hs-admin-more-alt"></i>
                          </a>

                          <div id="dropDown10" class="u-shadow-v31 g-pos-abs g-bottom-100x g-right-0 g-z-index-2 g-bg-white" aria-labelledby="dropDown10Invoker">
                            <ul class="list-unstyled g-nowrap mb-0">
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-pencil g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Edit
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-archive g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Archive
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-check g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Mark as Done
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-plus g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  New Task
                                </a>
                              </li>
                              <li>
                                <a class="d-flex align-items-center u-link-v5 g-bg-gray-light-v8--hover g-font-size-12 g-font-size-default--md g-color-gray-dark-v6 g-px-25 g-py-14" href="#!">
                                  <i class="hs-admin-trash g-font-size-18 g-color-gray-light-v6 g-mr-10 g-mr-15--md"></i>
                                  Delete
                                </a>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <!-- End Table -->
          </div>
        </div>

        <!-- Footer -->
        <footer id="footer" class="u-footer--bottom-sticky g-bg-white g-color-gray-dark-v6 g-brd-top g-brd-gray-light-v7 g-pa-20">
          <div class="row align-items-center">
            <!-- Footer Nav -->
            <div class="col-md-4 g-mb-10 g-mb-0--md">
              <ul class="list-inline text-center text-md-left mb-0">
                <li class="list-inline-item">
                  <a class="g-color-gray-dark-v6 g-color-secondary--hover" href="#!">FAQ</a>
                </li>
                <li class="list-inline-item">
                  <span class="g-color-gray-dark-v6">|</span>
                </li>
                <li class="list-inline-item">
                  <a class="g-color-gray-dark-v6 g-color-secondary--hover" href="#!">Support</a>
                </li>
                <li class="list-inline-item">
                  <span class="g-color-gray-dark-v6">|</span>
                </li>
                <li class="list-inline-item">
                  <a class="g-color-gray-dark-v6 g-color-secondary--hover" href="#!">Contact Us</a>
                </li>
              </ul>
            </div>
            <!-- End Footer Nav -->

            <!-- Footer Socials -->
            <div class="col-md-4 g-mb-10 g-mb-0--md">
              <ul class="list-inline g-font-size-16 text-center mb-0">
                <li class="list-inline-item g-mx-10">
                  <a href="#!" class="g-color-facebook g-color-secondary--hover">
                    <i class="fa fa-facebook-square"></i>
                  </a>
                </li>
                <li class="list-inline-item g-mx-10">
                  <a href="#!" class="g-color-google-plus g-color-secondary--hover">
                    <i class="fa fa-google-plus"></i>
                  </a>
                </li>
                <li class="list-inline-item g-mx-10">
                  <a href="#!" class="g-color-black g-color-secondary--hover">
                    <i class="fa fa-github"></i>
                  </a>
                </li>
                <li class="list-inline-item g-mx-10">
                  <a href="#!" class="g-color-twitter g-color-secondary--hover">
                    <i class="fa fa-twitter"></i>
                  </a>
                </li>
              </ul>
            </div>
            <!-- End Footer Socials -->

            <!-- Footer Copyrights -->
            <div class="col-md-4 text-center text-md-right">
              <small class="d-block g-font-size-default">&copy; 2018 Htmlstream. All Rights Reserved.</small>
            </div>
            <!-- End Footer Copyrights -->
          </div>
        </footer>
        <!-- End Footer -->
      </div>
    </div>
  </main>

  <!-- JS Global Compulsory -->
  <script src="../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../assets/vendor/jquery-migrate/jquery-migrate.min.js"></script>

  <script src="../../assets/vendor/popper.min.js"></script>
  <script src="../../assets/vendor/bootstrap/bootstrap.min.js"></script>

  <script src="../../assets/vendor/cookiejs/jquery.cookie.js"></script>


  <!-- jQuery UI Core -->
  <script src="../../assets/vendor/jquery-ui/ui/widget.js"></script>
  <script src="../../assets/vendor/jquery-ui/ui/version.js"></script>
  <script src="../../assets/vendor/jquery-ui/ui/keycode.js"></script>
  <script src="../../assets/vendor/jquery-ui/ui/position.js"></script>
  <script src="../../assets/vendor/jquery-ui/ui/unique-id.js"></script>
  <script src="../../assets/vendor/jquery-ui/ui/safe-active-element.js"></script>

  <!-- jQuery UI Helpers -->
  <script src="../../assets/vendor/jquery-ui/ui/widgets/menu.js"></script>
  <script src="../../assets/vendor/jquery-ui/ui/widgets/mouse.js"></script>

  <!-- jQuery UI Widgets -->
  <script src="../../assets/vendor/jquery-ui/ui/widgets/datepicker.js"></script>

  <!-- JS Plugins Init. -->
  <script src="../../assets/vendor/appear.js"></script>
  <script src="../assets/vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
  <script src="../assets/vendor/flatpickr/dist/js/flatpickr.min.js"></script>
  <script src="../../assets/vendor/malihu-scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
  <script src="../assets/vendor/chartist-js/chartist.min.js"></script>
  <script src="../assets/vendor/chartist-js-tooltip/chartist-plugin-tooltip.js"></script>
  <script src="../assets/vendor/fancybox/jquery.fancybox.min.js"></script>

  <!-- JS Unify -->
  <script src="../../assets/js/hs.core.js"></script>
  <script src="../../assets/js/helpers/hs.hamburgers.js"></script>
  <script src="../../assets/js/components/hs.datepicker.js"></script>
  <script src="../../assets/js/components/hs.dropdown.js"></script>
  <script src="../../assets/js/components/hs.scrollbar.js"></script>
  <script src="../../assets/js/helpers/hs.focus-state.js"></script>
  <script src="../../assets/js/components/hs.dropdown.js"></script>
  <script src="../assets/js/components/hs.side-nav.js"></script>
  <script src="../assets/js/components/hs.range-datepicker.js"></script>
  <script src="../assets/js/components/hs.area-chart.js"></script>
  <script src="../assets/js/components/hs.donut-chart.js"></script>
  <script src="../assets/js/components/hs.bar-chart.js"></script>
  <script src="../assets/js/components/hs.popup.js"></script>

  <!-- JS Custom -->
  <script src="../../assets/js/custom.js"></script>

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
</body>

</html>

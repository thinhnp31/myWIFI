<?php 
  include "../includes/defines.php";  
  include '../includes/db_connect.php'; 

  //  CHECK LOGIN
  //Check if this client has been logged in or not
  $logged_in = false;

  if (isset($_SESSION['email'])) { //if this client still has a session
    $logged_in = true;
  } else {
    if (isset($_COOKIE['email'])) { //if this client has cookie, 
      //may be I can remember him (or her)
      $token = $_COOKIE['email'];
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

  if (!$logged_in) { //If this client has not logged in 
    //redirect to login page
    $conn->close();
    header("Location: " . mywifi_admin . "login.php");
  }
  // END CHECK LOGIN

  //Get current year/month and last year/month
  $current_year = date('Y');
  $current_month = date('m');
  if ($current_month == 1) {
    $last_month = 12;
    $last_year = $current_year - 1;
  } else {
    $last_month = $current_month - 1;
    $last_year = $current_year;
  }

  // GET DATA
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

  //Get top os 
  $stmt = $conn->prepare("SELECT os, COUNT(os) AS count FROM connections WHERE NOT os = 'unknown' GROUP BY os ORDER BY count DESC");
  $stmt->execute();
  $result = $stmt->get_result();
  $top_os = "Unknown";
  $number_top_os = 0;

  //Get data to draw chart
  $os_series = [];
  $os_labels = [];
  while($row = $result->fetch_assoc()) {
  	if ($row['count'] > $number_top_os) {
  		$number_top_os = $row['count'];
  		$top_os = $row['os'];
  	}
  	array_push($os_series, $row['count']);
  	array_push($os_labels, $row['os']);
  }

  //Get top device 
  $stmt = $conn->prepare("SELECT device, COUNT(device) AS count FROM connections WHERE NOT device = 'unknown' GROUP BY device ORDER BY count DESC");
  $stmt->execute();
  $result = $stmt->get_result();
  $top_device = "Unknown";
  $number_top_device = 0;

  //Get data to draw chart
  $device_series = [];
  $device_labels = [];
  while($row = $result->fetch_assoc()) {
  	if ($row['count'] > $number_top_device) {
  		$number_top_device = $row['count'];
  		$top_device = $row['device'];
  	}
  	array_push($device_series, $row['count']);
  	array_push($device_labels, $row['device']);
  }

  //Get top browser 
  $stmt = $conn->prepare("SELECT browser, COUNT(browser) AS count FROM connections WHERE NOT browser = 'unknown' GROUP BY browser ORDER BY count DESC");
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $top_browser = "Unknown";
  $number_top_browser = 0;

  //Get data to draw chart
  $browser_series = [];
  $browser_labels = [];
  while($row = $result->fetch_assoc()) {
  	if ($row['count'] > $number_top_browser) {
  		$number_top_browser = $row['count'];
  		$top_browser = $row['browser'];
  	}
  	array_push($browser_series, $row['count']);
  	array_push($browser_labels, $row['browser']);
  }

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
  //   END GET DATA
?>

<!DOCTYPE html>
<html>

<head>
  <!-- Title -->
  <title>MyWIFI</title>

  <!-- Required Meta Tags Always Come First -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <!-- Favicon -->
  <link rel="shortcut icon" hrefavicon.ico">
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
   	<div class="row no-gutters g-pos-rel g-overflow-x-hidden">
   		<!-- Sidebar Nav -->
   		<?php include "includes/_sidenav.php"; ?>
   		<!-- End Sidebar Nav -->	

     	<div class="col g-ml-45 g-ml-0--lg g-pb-65--md">
   			<!-- MAIN CODE -->
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
					              <span class="g-hidden-sm-down g-font-weight-300 g-color-gray-dark-v6">SMS</span>
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
	                      <span class="g-font-size-16 g-color-gray-dark-v7">
	                      	<?php 
	                      		if ($delta_total_connections >= 0) {
	                          	echo "+" . $delta_total_connections;
	                        	} else {
	                          	echo "-" . abs($delta_total_connections);
	                        	}
	                        ?>
	                      </span>
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
					            	<span class="g-font-size-16 g-color-gray-dark-v7">
					            		<?php 
					              		if ($delta_total_clients >= 0) {
					                  	echo "+" . $delta_total_clients;
					                  } else {
					                  	echo "-" . abs($delta_total_clients);
					                        		}
					                ?>
					              </span>
					              <h5 class="g-font-weight-300 g-font-size-default g-font-size-16--md g-color-gray-dark-v6 mb-0">Người dùng</h5>
					            </div>

					            <div class="d-flex align-self-end ml-auto">
					            	<div class="d-block text-right g-font-size-16">
					              	<span class="d-block g-color-black">Giờ kết nối nhiều nhất</span>
					                <span class="d-block g-color-lightblue-v3">
					                	<?php echo $max_hour . ":00<br>" . $max_connection_hour ?>
					                </span>
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
	                  </header>

	                  <canvas id="client_chart" class="g-pos-rel g-line-height-0"></canvas>	                 	
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
	                </header>

	                <section>
	                	<canvas id="connection_chart" class="g-pos-rel g-line-height-0"></canvas>
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

	            <!-- Pie Charts -->

	            <!-- Browser -->
        			<canvas id="browser_chart" class="col-sm-6 col-lg-6 col-xl-4 g-mb-30">
        			</canvas>

        			<!-- OS -->
        			<canvas id="os_chart" class="col-sm-6 col-lg-6 col-xl-4 g-mb-30">
        			</canvas>

        			<!-- Device -->
        			<canvas id="device_chart" class="col-sm-6 col-lg-6 col-xl-4 g-mb-30">
        			</canvas>

	            <!-- End Pie Charts -->

			        <!-- Table -->
			        <div class="col-xl-12">
			         	<div class="table-responsive g-mb-40">
			         		<h3> 5 kết nối cuối cùng : </h3>
			           	<table class="table u-table--v3 g-color-black">
				      			<thead>
				            	<tr>
				              	<th class="g-px-30">
				                	<div class="media">
				                  	<div class="d-flex align-self-center">MAC</div>
						              </div>
				                </th>
				                <th class="g-px-30">
				                	<div class="media">
				                  	<div class="d-flex align-self-center">Thời gian</div>   
				                  </div>
				                </th>
					              <th class="g-px-30">
					              	<div class="media">
					                	<div class="d-flex align-self-center">Trình duyệt</div>
					                </div>
					              </th>
				                <th class="g-px-30">
				                	<div class="media">
				                  	<div class="d-flex align-self-center">Hệ điều hành</div>
				                  </div>
				                </th>
				                <th class="g-px-30">
				                	<div class="media">
				                  	<div class="d-flex align-self-center">Thiết bị</div>
				                  </div>
				                </th>
				                <th class="g-px-30">
				                	<div class="media">
				                  	<div class="d-flex align-self-center">Hình thức</div>
				                  </div>
				                </th>
				              </tr>
				            </thead>
										<tbody>
											<?php
												$stmt = $conn->prepare("SELECT * FROM `connections` WHERE YEAR(time) = ? AND MONTH(time) = ? ORDER BY time DESC LIMIT 5");
												$stmt->bind_param("ss",$current_year, $current_month);
        								$stmt->execute();
        								$result = $stmt->get_result();      
            						if ($result->num_rows > 0) {
            							while ($row = $result->fetch_assoc()) {
            					?>
            								<tr>
								            	<td class="g-px-30">
								              	<div class="media">
								                	<div class="media-body align-self-center text-left"><?php echo $row['mac'];?></div>
								                </div>
								              </td>
								              <td class="g-px-30"><?php echo $row['time'];?></td>
								             	<td class="g-px-30"><?php echo $row['browser'];?></td>
								              <td class="g-px-30"><?php echo $row['os'];?></td>
								              <td class="g-px-30"><?php echo $row['device'];?></td>
								              <td class="g-px-30">
								              	<?php 
								              		switch ($row['connection_type']) {
								              			case 'image':
								              				echo "Hình ảnh";
								              				break;
								              			case 'video':
								              				echo "Video";
								              				break;     
								              			case 'local_account':
								              				echo "Tài khoản";
								              				break;  
								              			case 'sms':
								              				echo "SMS";
								              				break;   	
								              			case 'social':
								              				echo "Mạng xã hội";
								              				break;		
								              			case 'survey':
								              				echo "Khảo sát";
								              				break;
								              			case 'voucher':
								              				echo "Voucher";
								              				break;
								              			default:
								              				echo "Không rõ";
								              				break;
								              		}
								              	?>							              	
								              </td>
								            </tr>
            					<?php
            							}
            						}
											?>					          	
					          </tbody>
			          	</table>
			        	</div>
			      	</div>
							<!-- End Table -->
			    	</div>
			    </div>
			  </div>
			  <!-- END MAIN CODE -->

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
 	<script src="assets/js/components/hs.side-nav.js"></script>
 	<script src="assets/js/helpers/hs.hamburgers.js"></script>
 	<script src="assets/js/components/hs.range-datepicker.js"></script>
 	<script src="assets/js/components/hs.datepicker.js"></script>
 	<script src="assets/js/components/hs.dropdown.js"></script>
 	<script src="assets/js/components/hs.scrollbar.js"></script>
 	<script src="assets/js/components/hs.area-chart.js"></script>
 	<script src="assets/js/components/hs.donut-chart.js"></script>
 	<script src="assets/js/components/hs.bar-chart.js"></script>
 	<script src="assets/js/helpers/hs.focus-state.js"></script>
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
 
   		// initialization of range datepicker
   		$.HSCore.components.HSRangeDatepicker.init('#rangeDatepicker, #rangeDatepicker2, #rangeDatepicker3');
  
   		// initialization of datepicker
   		$.HSCore.components.HSDatepicker.init('#datepicker',
     		{
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

  <!-- Draw connection_chart -->
  <script>
  	var ctx_connection_chart = document.getElementById("connection_chart").getContext('2d');
		var connection_chart = new Chart(ctx_connection_chart, {
    	type: 'line',
    	data: {
        labels: [
        	<?php
	        	$temp_string = '';
	          foreach($connection_labels as $labels)
	          	$temp_string .= '"'. $labels . '",';
	          echo chop($temp_string,",");
	        ?>
        ],
        datasets: [{
          label: 'Kết nối',
          data: [
						<?php
	          	$temp_string = '';
	            foreach($connection_series as $value)
	           		$temp_string .= $value .',';
	            echo chop($temp_string,",");
	          ?>
          ],
          backgroundColor: [
        		'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)'
          ],
          borderColor: [
          	'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)'
          ],
         	borderWidth: 3,
         	pointRadius : 0
        }]
    	},
    	options: {
    		responsive: true,
    		title: {
    			display: false,
    			text: "Thống kê kết nối"
    		},
    		tooltips: {
    			mode: "index",
    			intersect: false,
    		},
    		hover: {
    			mode: "nearest",
    			intersect: true,
    		},
        scales: {
        	xAxes: [{
          	display: true,
          	scaleLabel: {
          		display: true,
          		labelString: 'Ngày'
          	}
        	}],
          yAxes: [{
          	display: true,
          	scaleLabel: {
          		display: true,
          		labelString: 'Kết nối'
          	}
        	}]
        }
    	}
		});
  </script>
  <!-- End Drawing connection_chart -->

  <!-- Draw client_chart -->
  <script>
  	var ctx_client_chart = document.getElementById("client_chart").getContext('2d');
		var client_chart = new Chart(ctx_client_chart, {
    	type: 'line',
    	data: {
        labels: [
        	<?php
	        	$temp_string = '';
	          foreach($client_labels as $labels)
	          	$temp_string .= '"'. $labels . '",';
	          echo chop($temp_string,",");
	        ?>
        ],
        datasets: [{
          label: 'Người dùng',
          data: [
						<?php
	          	$temp_string = '';
	            foreach($client_series as $value)
	           		$temp_string .= $value .',';
	            echo chop($temp_string,",");
	          ?>
          ],
          backgroundColor: [
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)',
        		'rgba(255, 99, 132, 0.2)'
          ],
          borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
          	'rgba(255,99,132,1)'
          ],
         	borderWidth: 3,
         	pointRadius : 0
        }]
    	},
    	options: {
    		responsive: true,
    		title: {
    			display: false,
    			text: "Thống kê người dùng"
    		},
    		tooltips: {
    			mode: "index",
    			intersect: false,
    		},
    		hover: {
    			mode: "nearest",
    			intersect: true,
    		},
        scales: {
        	xAxes: [{
          	display: true,
          	scaleLabel: {
          		display: false,
          		labelString: 'Ngày'
          	}
        	}],
          yAxes: [{
          	display: true,
          	scaleLabel: {
          		display: false,
          		labelString: 'Người dùng'
          	}
        	}]
        }
    	}
		});
	</script>
  <!-- End Drawing client_chart -->

  <!-- Draw browser_chart -->
  <script>
  	var ctx_browser_chart = document.getElementById("browser_chart").getContext('2d');
		var browser_chart = new Chart(ctx_browser_chart, {
    	type: 'pie',
    	data: {
        labels: [
        	<?php
	        	$temp_string = '';
	          foreach($browser_labels as $labels)
	          	$temp_string .= '"'. $labels . '",';
	          echo chop($temp_string,",");
	        ?>
        ],
        datasets: [{
          label: 'Trình duyệt',
          data: [
						<?php
	          	$temp_string = '';
	            foreach($browser_series as $value)
	           		$temp_string .= $value .',';
	            echo chop($temp_string,",");
	          ?>
          ],
          backgroundColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
        		'rgba(255, 99, 132, 1)'
          ],
          borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
          	'rgba(255,99,132,1)'
          ]
        }]
    	},
    	options: {
    		responsive: true,
    		title: {
    			display: true,
    			text: "Trình duyệt",
    			fontSize: 30,
    			fontStyle: "normal",
    		}
    	}
		});
	</script>
  <!-- End Drawing browser_chart -->

  <!-- Draw os_chart -->
  <script>
  	var ctx_os_chart = document.getElementById("os_chart").getContext('2d');
		var os_chart = new Chart(ctx_os_chart, {
    	type: 'pie',
    	data: {
        labels: [
        	<?php
	        	$temp_string = '';
	          foreach($os_labels as $labels)
	          	$temp_string .= '"'. $labels . '",';
	          echo chop($temp_string,",");
	        ?>
        ],
        datasets: [{
          label: 'Hệ điều hành',
          data: [
						<?php
	          	$temp_string = '';
	            foreach($os_series as $value)
	           		$temp_string .= $value .',';
	            echo chop($temp_string,",");
	          ?>
          ],
          backgroundColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
        		'rgba(255, 99, 132, 1)'
          ],
          borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
          	'rgba(255,99,132,1)'
          ]
        }]
    	},
    	options: {
    		responsive: true,
    		title: {
    			display: true,
    			text: "Hệ điều hành",
    			fontSize: 30,
    			fontStyle: "normal",
    		}
    	}
		});
	</script>
  <!-- End Drawing os_chart -->

  <!-- Draw device_chart -->
  <script>
  	var ctx_device_chart = document.getElementById("device_chart").getContext('2d');
		var device_chart = new Chart(ctx_device_chart, {
    	type: 'pie',
    	data: {
        labels: [
        	<?php
	        	$temp_string = '';
	          foreach($device_labels as $labels)
	          	$temp_string .= '"'. $labels . '",';
	          echo chop($temp_string,",");
	        ?>
        ],
        datasets: [{
          label: 'Thiết bị',
          data: [
						<?php
	          	$temp_string = '';
	            foreach($device_series as $value)
	           		$temp_string .= $value .',';
	            echo chop($temp_string,",");
	          ?>
          ],
          backgroundColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
        		'rgba(255, 99, 132, 1)'
          ],
          borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
          	'rgba(255,99,132,1)'
          ]
        }]
    	},
    	options: {
    		responsive: true,
    		title: {
    			display: true,
    			text: "Thiết bị",
    			fontSize: 30,
    			fontStyle: "normal"
    		}
    	}
		});
	</script>
  <!-- End Drawing device_chart -->
</body>

</html>
<?php 
  $conn->close(); 
?>
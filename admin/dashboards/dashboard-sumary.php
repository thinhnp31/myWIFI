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
    //   END GET DATA
    $conn->close();
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
</head>

<body>
	<!-- Header -->
  	<?php include "../includes/_header.php"; ?>
  	<!-- End Header -->

  	<main class="container-fluid px-0 g-pt-65">
    	<div class="row no-gutters g-pos-rel g-overflow-x-hidden">
      		<!-- Sidebar Nav -->
      		<?php include "../includes/_sidenav.php"; ?>
      		<!-- End Sidebar Nav -->	

	      	<div class="col g-ml-45 g-ml-0--lg g-pb-65--md">
	      		<!-- MAIN CODE -->

	      		<!-- END MAIN CODE -->

		        <!-- Footer -->
		        <?php include "../includes/_footer.php"; ?>
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
  	<script src="../assets/js/custom.js"></script>

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
</body>

</html>
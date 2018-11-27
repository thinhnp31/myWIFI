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
  <link rel="shortcut icon" href="favicon.ico">
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
  	<?php include "includes/_header.php"; ?>
  	<!-- End Header -->

  	<main class="container-fluid px-0 g-pt-65">
    	<div class="row no-gutters g-pos-rel g-overflow-x-hidden">
      		<!-- Sidebar Nav -->
      		<?php include "includes/_sidenav.php"; ?>
      		<!-- End Sidebar Nav -->	

	      	<div class="col g-ml-45 g-ml-0--lg g-pb-65--md">
	      		<!-- MAIN CODE -->
            <div class="g-pa-20">
              <h1 class="g-font-weight-300 g-font-size-28 g-color-black g-mb-30">Danh sách tài khoản</h1>

              <div class="media-md align-items-center g-mb-30">
                <div class="d-flex g-mb-15 g-mb-0--md">
                  <a class="u-tags-v1 text-center g-width-100 g-brd-around g-brd-teal-v2 g-bg-teal-v2 g-font-weight-400 g-color-white g-rounded-50 g-py-4 g-px-15" href="create_local_account.php">Tạo mới</a>
                </div>
                <div class="media d-md-flex align-items-center ml-auto">
                  <form method="GET" action="#">
                    <div class="input-group g-pos-rel g-width-320--md">                  
                      <input class="form-control g-font-size-default g-brd-gray-light-v7 g-brd-lightblue-v3--focus g-rounded-20 g-pl-20 g-pr-50 g-py-10" type="text" placeholder="username" name="username">
                      <button class="btn g-pos-abs g-top-0 g-right-0 g-z-index-2 g-width-60 h-100 g-bg-transparent g-font-size-16 g-color-primary g-color-secondary--hover rounded-0" type="submit">
                        <i class="hs-admin-search g-absolute-centered"></i>
                      </button>                  
                    </div>
                  </form>
                </div>
              </div>

              <div class="table-responsive g-mb-40">
                <table class="table u-table--v3 g-color-black">
                  <thead>
                    <tr>
                      <th>Tên đăng nhập</th>
                      <th>Họ và tên</th>
                      <th>Số thiết bị</th>
                      <th>Ngày hết hạn</th>
                      <th>Lần đăng nhập cuối</th>
                      <th>Trạng thái</th>
                      <th>Hành động</th>
                    </tr>
                </thead>

                <tbody>
                  <?php
                    if (isset($_GET['username'])) {
                      $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
                      $stmt->bind_param('s', $_GET['username']);
                    } else {
                      $stmt = $conn->prepare("SELECT * FROM users "); 
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();      
                        if ($result->num_rows > 0) { 
                          while ($row = $result->fetch_assoc()) {
                            $stmt = $conn->prepare("SELECT * FROM sessions WHERE username = ? ORDER BY time DESC");
                            $stmt->bind_param('s', $row['username']);
                            $stmt->execute();
                            $result2 = $stmt->get_result();
                            $device = $result2->num_rows;
                            $row2 = $result2->fetch_assoc();
                            $last_time = $row2['time'];
                  ?>
                            <tr>
                              <td><?php echo $row['username']; ?></td>
                              <td><?php echo $row['fullname']; ?></td>
                              <td>
                                <?php 
                                if ($row['device_limit'] > 0) {
                                  echo $device . "/" . $row['device_limit']; 
                                } else {
                                  echo $device . "/ - ";
                                }
                                
                                ?>
                                  
                              </td>
                              <td><?php echo $row['expiry_date']; ?></td>
                                <td><?php echo $last_time; ?></td>
                              <td>
                                <?php 
                                  switch ($row['user_status']) {
                                    case 'active':
                                      echo "<span style='color:green'>Active</span>";
                                      break;
                                    case 'expired':
                                      echo "<span style='color:red'>Hết hạn</span>";
                                      break;
                                    default:
                                      echo "<span style='color:black'>Unknown</span>";
                                      break;
                                  }
                                ?>
                              </td>
                              <td> 
                                <a class="u-tags-v1 text-center g-width-100 g-brd-around g-brd-teal-v2 g-bg-teal-v2 g-font-weight-400 g-color-white g-rounded-50 g-py-4 g-px-15" href="local_account_detail.php?user_id=<?php echo $row['user_id'];?>">Chi tiết</a>
                                <a class="u-tags-v1 text-center g-width-100 g-brd-around g-brd-primary g-bg-primary g-font-weight-400 g-color-white g-rounded-50 g-py-4 g-px-15" href="delete_local_account.php?user_id=<?php echo $row['user_id'];?>">Xóa</a>
                              </td>
                            </tr>

                  <?php
                          }
                        } else {
                          echo "<span style='color:red'>Không có tài khoản</span>";
                        }
                  ?>
                </tbody>
              </table>
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
</body>

</html>
<?php 
  $conn->close(); 
?>
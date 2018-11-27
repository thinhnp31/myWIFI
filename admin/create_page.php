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

    $msg = "";
    $type = "";
    $uploadOk = 0;
    if (isset($_POST['submit'])) {
      //HANDLE FILE UPLOAD
      //Check background type :
      $background_extension = strtolower(pathinfo($_FILES["background"]["name"],PATHINFO_EXTENSION));
      $check = getimagesize($_FILES["background"]["tmp_name"]);
      if($check !== false) {
        $uploadOk = 1;
      } else {
        $msg .= "Background không phải là file ảnh <br>";
        $uploadOk = 0;
      }
      //Check backround size 
      if ($_FILES["background"]["size"] > 5000000) {
        $msg .= "Background có kích thước quá lớn. Vui lòng chọn ảnh <= 5MB";
        $uploadOk = 0;
      } else {
        $uploadOk = 1;
      }
      //Check logo type :
      $logo_extension = strtolower(pathinfo($_FILES["logo"]["name"],PATHINFO_EXTENSION));
      $check = getimagesize($_FILES["logo"]["tmp_name"]);
      if($check !== false) {
        $uploadOk = 1;
      } else {
        $msg .= "Logo không phải là file ảnh <br>";
        $uploadOk = 0;
      }
      //Check backround size 
      if ($_FILES["logo"]["size"] > 500000) {
        $msg .= "Logo có kích thước quá lớn";
        $uploadOk = 0;
      } else {
        $uploadOk = 1;
      }

      if ($uploadOk == 1) {
        //Upload file
        $target_background = "../images/background/" . uniqid() . '.' . $background_extension;
        while (file_exists($target_background)) {
          $target_background = "../images/background/" . uniqid() . '.' . $background_extension;
        }
        $target_logo = "../images/logo/" . uniqid() . '.' . $logo_extension;
        while (file_exists($target_logo)) {
          $target_logo = "../images/logo/" . uniqid() . '.' . $logo_extension;
        }
        $background_status = move_uploaded_file($_FILES["background"]["tmp_name"], $target_background);
        $logo_status = move_uploaded_file($_FILES["logo"]["tmp_name"], $target_logo);
        if ($background_status && $logo_status) {
          //If success Uploading FILES
          $page_name = $_POST['page_name'];
          $connection_type = $_POST['connection_type'];          
          $profile_id = $_POST['profile_id'];
          $welcome = $_POST['welcome'];
          $background_name = basename($target_background);
          $logo_name = basename($target_logo);

          //check duplicate page name
          $stmt = $conn->prepare("SELECT * FROM pages WHERE page_name = ?");
          $stmt->bind_param('s', $page_name);
          $stmt->execute();
          $result = $stmt->get_result();
          if ($result->num_rows > 0) {
            $msg .= "Tên trang đã tồn tại";
            $type = "danger";
          } else {
            //create page
            $stmt = $conn->prepare("INSERT INTO pages (page_name, background, logo, connection_type, profile_id, welcome) VALUES (?,?,?,?,?,?) ");            
            $stmt->bind_param('ssssis', $page_name, $background_name, $logo_name, $connection_type, $profile_id, $welcome);
            $stmt->execute();
            $msg = "Tạo trang thành công";
            $type = "success";
          }          
        } else {
          $msg .= "Có lỗi khi upload file";
          $type = "danger";
        }
      } else {
        $msg .= "Tạo trang chưa thành công";
        $type = "danger";
      }
      //END BACKGROUND UPLOAD
    }
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

  <!-- JQuery for Image Preview -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
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
              <!-- Notification Area -->
              <?php
                if (isset($msg) && isset($type)) {
              ?>
                  <div class="alert alert-<?php echo $type; ?>" role="alert">
                    <?php echo $msg; ?>
                  </div>
              <?php
                }
              ?> 
              <h1 class="g-font-weight-300 g-font-size-28 g-color-black g-mb-30">Tạo trang đăng nhập</h1>
              <div class="row">
                <div class="col-sm-7">
                <!-- Form -->
                  <form class="g-py-15" method="POST" enctype="multipart/form-data" action="#" onsubmit="return form_validate()">
                    <div class="mb-4">
                      <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Tên trang : </label>
                      <input class="form-control g-color-black g-bg-white g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" name="page_name" required>
                    </div>

                    <div class="mb-4">
                      <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Tiêu đề : </label>
                      <input class="form-control g-color-black g-bg-white g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" name="welcome">
                    </div>

                    <div class="row">
                      <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                        <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Hình nền:</label>
                        <input type="file" required name="background" id = "background" accept="image/*" onchange="readURLBackground(this)">
                        <img id="previewBackgroundHolder" width="80%">
                      </div>

                      <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                        <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Logo:</label>
                        <input type="file" required name="logo" id = "logo" accept="image/*" onchange="readURLLogo(this)">   
                        <img id="previewLogoHolder" width="80%">  
                      </div>
                    </div>     
                      
                    <div class="mb-4" >
                      <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Hình thức:</label>
                      <select class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover" style="padding-top: 5px;" name="connection_type" required id="connection_type" oninput="display_guide();">
                        <option value="image">Hình ảnh</option>
                        <option value="video">Video</option>
                        <option value="local_account">Tài Khoản</option>
                        <option value="sms">SMS</option>
                        <option value="social">Mạng xã hội</option>
                        <option value="survey">Khảo sát</option>
                        <option value="voucher">Voucher</option>
                      </select>                           
                    </div>

                    <div class="mb-4">
                      <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Profile : </label>
                      <select class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover" style="padding-top: 5px;" name="profile_id" required id="profile_id">
                        <?php
                          $stmt = $conn->prepare("SELECT * FROM profiles");
                          $stmt->execute();
                          $result = $stmt->get_result();
                          while($row = $result->fetch_assoc()) {
                            if ($row['profile_name'] != '') {
                        ?>
                              <option value=<?php echo $row['profile_id'];?>><?php echo $row['profile_name']; ?></option>
                        <?php
                            }
                          }
                        ?>                          
                      </select>
                    </div>

                    <div class="mb-4 text-center">
                      <input type="submit" class="btn btn-md u-btn-secondary rounded g-py-13 g-px-50" name="submit" value="Tạo">
                    </div>
                  </form>
                  <!-- End Form -->
                </div>

                <div class="col">
                  <h2>Máy tính</h2>
                  <div id="guide_pc" class="border border-dark"></div>
                  <h2>Điện thoại</h2>
                  <div class="text-center">
                    <div class="border border-dark" style="width: 40%; margin: auto;" id="guide_mobile">
                    </div>
                  </div>
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

        display_guide();
    	});
  	</script>
    <script type="text/javascript">
    form_validate = function()  {
      var password = document.getElementById("password").value;
      var confirmed_password = document.getElementById("confirmed_password").value;

      if (password == confirmed_password) {
        return true;
      } else {        
        document.getElementById("password_holder").className = "col-xs-12 col-sm-6 mb-4 form-group u-has-error-v1";
        document.getElementById("confirmed_password_holder").className = "col-xs-12 col-sm-6 mb-4 form-group u-has-error-v1";
        document.getElementById("password_helper").style.display = "block";   
        return false;
      }
    }
  </script>

  <script>
    function readURLBackground(input) {      
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById("previewBackgroundHolder").src=e.target.result;
        }
        reader.readAsDataURL(input.files[0]); 
      }
    }
    function readURLLogo(input) {      
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById("previewLogoHolder").src=e.target.result;
        }
        reader.readAsDataURL(input.files[0]); 
      }
    }
  </script>

  <script>
    function display_guide() {
      var connection_type = document.getElementById("connection_type").value;
      switch(connection_type) {
        case "image":
          $("#guide_pc").load("includes/guide/image.html");
          $("#guide_mobile").load("includes/guide/image.html");
          document.getElementById("ad_url").style.display="block";
          document.getElementById("ad_url_container").style.display="block";
          break;
        case "video":
          document.getElementById("guide_pc").src="includes/images/video_pc.png";
          document.getElementById("guide_mobile").src="includes/images/video_mobile.png";
          document.getElementById("ad_url").style.display="block";
          document.getElementById("ad_url_container").style.display="block";
          break;
        case "local_account":
          document.getElementById("guide_pc").src="includes/images/local_account_pc.png";
          document.getElementById("guide_mobile").src="includes/images/local_account_mobile.png";
          document.getElementById("ad_url").style.display="none";
          document.getElementById("ad_url_container").style.display="none";
          break;
        case "sms":
          document.getElementById("guide_pc").src="includes/images/sms_pc.png";
          document.getElementById("guide_mobile").src="includes/images/sms_mobile.png";
          document.getElementById("ad_url").style.display="none";
          document.getElementById("ad_url_container").style.display="none";
          break;
        case "social":
          document.getElementById("guide_pc").src="includes/images/social_pc.png";
          document.getElementById("guide_mobile").src="includes/images/social_mobile.png";
          document.getElementById("ad_url").style.display="none";
          document.getElementById("ad_url_container").style.display="none";
          break;
        case "survey":
          document.getElementById("guide_pc").src="includes/images/survey_pc.png";
          document.getElementById("guide_mobile").src="includes/images/survey_mobile.png";
          document.getElementById("ad_url").style.display="none";
          document.getElementById("ad_url_container").style.display="none";
          break;
        case "voucher":
          document.getElementById("guide_pc").src="includes/images/voucher_pc.png";
          document.getElementById("guide_mobile").src="includes/images/voucher_mobile.png";
          document.getElementById("ad_url").style.display="none";
          document.getElementById("ad_url_container").style.display="none";
          break;
        default:
          break;
      }
    }
  </script>

</body>

</html>
<?php 
  $conn->close(); 
?>
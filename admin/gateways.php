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

    if (isset($_GET['submit'])) {
      switch ($_GET['submit']) {
        case 'Thêm':
          $gw_name = $_GET['gw_name'];
          $gw_sn = $_GET['gw_sn'];
          $gwgroup_id = $_GET['gwgroup_id'];

          //Check duplicate Name 
          $stmt = $conn->prepare("SELECT * FROM gateways WHERE gw_name = ?");
          $stmt->bind_param('s', $gw_name);
          $stmt->execute();
          $result = $stmt->get_result();
          if ($result->num_rows > 0) {
            $msg = "Tên thiết bị đã tồn tại";
            $type = "danger";
            header('Location: gateways.php?msg=' . $msg . '&type=' . $type);  
          } else { //Check duplicate Serial Number
            $stmt = $conn->prepare("SELECT * FROM gateways WHERE gw_sn = ?");
            $stmt->bind_param('s', $gw_sn);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
              $msg = "Serial Number đã tồn tại";
              $type = "danger";
              header('Location: gateways.php?msg=' . $msg . '&type=' . $type);  
            } else {
              $stmt = $conn->prepare("INSERT INTO gateways (gw_name, gw_sn, gwgroup_id) VALUES (?, ?, ?);");
              $stmt->bind_param('ssi', $gw_name, $gw_sn, $gwgroup_id);
              $stmt->execute(); 
              $msg = "Thêm thiết bị thành công";
              $type = "success";
              header('Location: gateways.php?msg=' . $msg . '&type=' . $type);          
            }  
          }         
          break;
        case 'Xóa':
          $id = $_GET['id'];
          $stmt = $conn->prepare("DELETE FROM gateways WHERE id = ?");
          $stmt->bind_param('i', $id);
          $stmt->execute(); 
          $msg = "Xóa thiết bị thành công";
          $type = "success";
          header('Location: gateways.php?msg=' . $msg . '&type=' . $type);
          break;
        case 'Sửa':
          $gw_name = $_GET['gw_name'];
          $id = $_GET['id'];
          $gwgroup_id = $_GET['gwgroup_id'];

          //Check duplicate Group Name 
          $stmt = $conn->prepare("SELECT * FROM gateways WHERE gw_name = ? AND NOT id = ?");
          $stmt->bind_param('si', $gw_name, $id);
          $stmt->execute();
          $result = $stmt->get_result();
          if ($result->num_rows > 0) {
            $msg = "Tên nhóm đã tồn tại";
            $type = "danger";
            header('Location: gateways.php?msg=' . $msg . '&type=' . $type);          
          } else {
            $stmt = $conn->prepare("SELECT * FROM gateways WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute(); 
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $old_gw_name = $row['gw_name'];
            $old_gwgroup_id = $row['gwgroup_id'];

            if (($old_gw_name == $gw_name) && ($old_gwgroup_id == $gwgroup_id)) {
              $msg = "Không có gì thay đổi cả";
              $type = "success";
              header('Location: gateways.php?msg=' . $msg . '&type=' . $type);
            } else {
              $stmt = $conn->prepare("UPDATE gateways SET gw_name = ?, gwgroup_id = ? WHERE id = ?");
              $stmt->bind_param('sii', $gw_name, $gwgroup_id, $id);
              $stmt->execute(); 
              $msg = "Chỉnh sửa nhóm thành công";
              $type = "success";
              header('Location: gateways.php?msg=' . $msg . '&type=' . $type);
            }
          }
          break;
        default:
          
          break;
      }
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
                if (isset($_GET['msg'])) {
                  $msg = $_GET['msg'];
                }
                if (isset($_GET['type'])) {
                  $type = $_GET['type'];
                }
                if (isset($msg) && isset($type)) {
              ?>
                  <div class="alert alert-<?php echo $type; ?>" role="alert">
                    <?php echo $msg; ?>
                  </div>
              <?php
                }
              ?>
              <h1 class="g-font-weight-300 g-font-size-28 g-color-black g-mb-30">Danh sách thiết bị</h1>

              <div class="media-md align-items-center g-mb-30">
                <div class="d-flex g-mb-15 g-mb-0--md">
                  <a class="u-tags-v1 text-center g-width-130 g-brd-around g-brd-teal-v2 g-bg-teal-v2 g-font-weight-400 g-color-white g-rounded-50 g-py-4 g-px-15" onclick="document.getElementById('add_gateway').style.display = 'table-row';">Thêm thiết bị</a>
                </div>
                
                <div class="media d-md-flex align-items-center ml-auto">
                  <form method="GET" action="#">
                    <div class="input-group g-pos-rel g-width-320--md">                  
                      <select class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover" style="padding-top: 5px;" name="gwgroup_id" required>
                          <?php
                            $stmt = $conn->prepare("SELECT * FROM gateway_groups");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                          ?>
                            <option value=<?php echo $row['gwgroup_id'];?> ><?php echo $row['gwgroup_name']; ?></option>
                          <?php
                            }
                          ?>
                        <option value=''>Tất cả</option>
                      </select>
                      <button class="btn g-pos-abs g-top-0 g-right-0 g-z-index-2 g-width-60 h-100 g-bg-transparent g-font-size-16 g-color-primary g-color-secondary--hover rounded-0" type="submit">
                        <i class="hs-admin-filter g-absolute-centered"></i>
                      </button>                     
                    </div>
                  </form>
                </div>
              </div>

              <div class="table-responsive g-mb-40">
                <table class="table u-table--v3 g-color-black">
                  <thead>
                    <tr>
                      <th>Tên thiết bị</th>
                      <th>Serial Number</th>
                      <th>Nhóm</th>
                      <th>Hành động</th>
                    </tr>
                </thead>

                <tbody>
                  <form method="GET" action="#" >    
                    <tr id="add_gateway" style="display:none">
                      <td>
                        <input type="text" required name="gw_name">                        
                      </td>
                      <td>
                        <input type="text" required name="gw_sn">
                      </td>
                      <td>
                        <select class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover" style="padding-top: 5px;" name="gwgroup_id" required>
                          <?php
                            $stmt = $conn->prepare("SELECT * FROM gateway_groups");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                          ?>
                            <option value=<?php echo $row['gwgroup_id'];?> ><?php echo $row['gwgroup_name']; ?></option>
                          <?php
                            }
                          ?>
                        </select>
                      </td>
                      <td>
                        <input class="u-tags-v1 text-center g-width-100 g-brd-around g-brd-teal-v2 g-bg-teal-v2 g-font-weight-400 g-color-white g-rounded-50 g-py-4 g-px-15" type="submit" name="submit" value="Thêm">
                      </td>
                    </tr>                  
                  </form>
                  <?php
                    if (isset($_GET['gwgroup_id']) && ($_GET['gwgroup_id'] != '')) {
                      $stmt = $conn->prepare("SELECT * FROM gateways RIGHT JOIN gateway_groups ON gateway_groups.gwgroup_id = gateways.gwgroup_id WHERE gateway_groups.gwgroup_id = ? ");
                      $stmt->bind_param('i', $_GET['gwgroup_id']);
                    } else {
                      $stmt = $conn->prepare("SELECT * FROM gateways RIGHT JOIN gateway_groups ON gateway_groups.gwgroup_id = gateways.gwgroup_id "); 
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();      
                        if ($result->num_rows > 0) { 
                          while ($row = $result->fetch_assoc()) {
                            if ($row['gw_name'] != '') {
                  ?>
                              <form method="GET" action="#">
                              <tr>                            
                                <td>
                                  <input type="text" value="<?php echo $row['gw_name']; ?>" name='gw_name' style="border:none;" readonly onclick="this.readOnly = false; document.getElementById('edit_<?php echo $row['id']; ?>').style.display = 'table-row';">
                                </td>
                                <td><?php echo $row['gw_sn']; ?></td>
                                <td>
                                  <select class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover" style="padding-top: 5px;border:none; background-color: white;" name="gwgroup_id" required  readonly onclick="this.readOnly = false; document.getElementById('edit_<?php echo $row['id']; ?>').style.display = 'table-row';">
                                    <?php
                                      $stmt2 = $conn->prepare("SELECT * FROM gateway_groups");
                                      $stmt2->execute();
                                      $result2 = $stmt2->get_result();
                                      while ($row2 = $result2->fetch_assoc()) {
                                    ?>
                                      <option value=<?php echo $row2['gwgroup_id'];?> <?php if ($row2['gwgroup_id'] == $row['gwgroup_id']) echo 'selected';?> ><?php echo $row2['gwgroup_name']; ?></option>
                                    <?php
                                      }
                                    ?>  
                                  </select>
                                </td>
                                <td>
                                  <input type="hidden" name="id" value=<?php echo $row['id']?>>
                                  <input type="submit" class="u-tags-v1 text-center g-width-100 g-brd-around g-brd-teal-v2 g-bg-teal-v2 g-font-weight-400 g-color-white g-rounded-50 g-py-4 g-px-15" name="submit" value="Sửa" id="edit_<?php echo$row['id'];?>" style="display:none;">
                                  <a class="u-tags-v1 text-center g-width-100 g-brd-around g-brd-primary g-bg-primary g-font-weight-400 g-color-white g-rounded-50 g-py-4 g-px-15" href="?submit=Xóa&id=<?php echo $row['id'];?>">Xóa</a>
                                </td>
                              
                              </tr>
                              </form>
                  <?php     
                            }                       
                          }
                        } else {
                          echo "<span style='color:red'>Không có Thiết bị</span>";
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

    <script type="text/javascript">
      displayAddGroup = function() {

      }
    </script>
</body>

</html>
<?php 
  $conn->close(); 
?>
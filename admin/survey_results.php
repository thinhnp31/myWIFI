<!-- set bodyTheme = "u-card-v1" -->
<?php 
include "includes/defines.php";  
include 'includes/db_connect.php'; 

    //Check if this client has been logged in or not
$logged_in = false;


    if (isset($_SESSION['email']) && isset($_SESSION['fullname'])) { //if this client still has a session
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

    //if user submit  a form
        if (isset($_POST['submit'])) {
          switch ($_POST['submit']) {
        case 'Làm mới': //if user re-new a session
          //get MAC
        $mac = $_POST['mac'];

        $stmt = $conn->prepare("SELECT * FROM sessions WHERE mac = ?");
        $stmt->bind_param("s", $mac);
        $stmt->execute();
        $result = $stmt->get_result();

          if ($result->num_rows > 0) {//if mac exists
            $now = date("Y-m-d H:i:s");
            $stmt = $conn->prepare("UPDATE sessions SET incoming = 0, outgoing = 0, time = ? WHERE mac  = ?");
            $stmt->bind_param("ss", $now, $mac);
            $stmt->execute();
            $msg = "Làm mới thành công : " . $mac . " " . $now;
            $type = "success";
          } else { //if NOT
            $msg = "Có lỗi trong quá trình thực hiện";
            $type = "danger";
          }
          break;
        case 'Xóa': //if user delete a session
          //get parameters
        $mac = $_POST['mac'];

          //delete profile
        $stmt = $conn->prepare("DELETE FROM sessions WHERE mac = ?");
        $stmt->bind_param("s", $mac);
        $stmt->execute();
        $msg = "Xóa kết nối thành công";
        $type = "success";
        break;
        default:
          # code...
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

          <h1 class="g-font-weight-300 g-font-size-28 g-color-black g-mb-30">Kết quả khảo sát</h1>

          <div class="media-md align-items-center g-mb-30">
            <div class="media d-md-flex align-items-center ml-auto">
              <form method="GET" action="#">
                <div class="input-group g-pos-rel g-width-320--md">                  
                  <select name="survey_id" class="form-control g-font-size-default g-brd-gray-light-v7 g-brd-lightblue-v3--focus g-rounded-20 g-pl-20 g-pr-50 g-py-5" >
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM surveys");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                      if ($row['survey_name'] != "") {
                        ?>
                        <option value="<?php echo $row['survey_id'];?>"><?php echo $row['survey_name'];?></option>
                        <?php
                      }
                    }
                    ?>
                  </select>
                  <button class="btn g-pos-abs g-top-0 g-right-0 g-z-index-2 g-width-60 h-100 g-bg-transparent g-font-size-16 g-color-primary g-color-secondary--hover rounded-0" type="submit">
                    <i class="hs-admin-filter g-absolute-centered"></i>
                  </button>                  
                </div>
              </form>
            </div>
          </div>

          <div class="container">                        
            <?php
            if (isset($_GET['survey_id']) && ($_GET['survey_id'] != '')) {
              $stmt = $conn->prepare("SELECT * FROM surveys WHERE survey_id = ?");
              $stmt->bind_param('i', $_GET['survey_id']);
            } else {
              $stmt = $conn->prepare("SELECT * FROM surveys LIMIT 1"); 
            }
            $stmt->execute();
            $result = $stmt->get_result();      
            if ($result->num_rows > 0) { 
              $row = $result->fetch_assoc();
              $survey_id = $row['survey_id'];
              $survey_name = $row['survey_name'];
              $stmt = $conn->prepare("SELECT * FROM questions WHERE survey_id = ?");
              $stmt->bind_param('i', $survey_id);
              $stmt->execute();
              $result = $stmt->get_result();
              echo "<h2>Khảo sát : " . $survey_name . "</h2>";
              $colors = ["",
              "'rgba(255,64,64,1)'",
              "'rgba(102,204,204,1)'",
              "'rgba(8,141,165,1)'",
              "'rgba(2,42,49,1)'",
              "'rgba(1,67,81,1)'"];
              $color_index = 0;
              while ($row = $result->fetch_assoc()) {
                $question = $row['question'];
                $question_id = $row['question_id'];
                $question_type = $row['question_type'];
                ?>
                <div class="row my-4">
                  <div class="col">
                    <div class="row"><h3><?php echo $question;?></h3></div>

                    <?php
                    if ($question_type == "text") {
                      ?>
                      <div class="table-responsive g-mb-40">
                        <table class="table u-table--v3 g-color-black">
                          <?php
                          $stmt = $conn->prepare("SELECT * FROM answers WHERE question_id = ?");
                          $stmt->bind_param('i', $question_id);
                          $stmt->execute();
                          $result2 = $stmt->get_result();
                          while ($row2 = $result2->fetch_assoc()) {
                            ?>
                            <tr>
                              <td style="text-align: left"><?php echo $row2['answer'];?></td>
                            </tr>
                            <?php
                          }
                          ?>                      
                        </table>
                      </div>
                      <?php

                    } 
                    if ($question_type != "text") {
                    //Prepare the X-Axes Labels (answers) and Y-Axes (data)
                      $color_index = $color_index + 1;
                      $answers = [];
                      $data = [];
                      if ($row['answer1'] != "") {
                        array_push($answers, $row['answer1']);
                        $stmt = $conn->prepare("SELECT COUNT(answer_id) AS count FROM answers WHERE answer = ? AND question_id = ?");
                        $stmt->bind_param('si', $row['answer1'], $question_id);
                        $stmt->execute();
                        $result4 = $stmt->get_result();
                        $row4 = $result4->fetch_assoc();
                        array_push($data, $row4['count']);
                      }
                      if ($row['answer2'] != "") {
                        array_push($answers, $row['answer2']);
                        $stmt = $conn->prepare("SELECT COUNT(answer_id) AS count FROM answers WHERE answer = ? AND question_id = ?");
                        $stmt->bind_param('si', $row['answer2'], $question_id);
                        $stmt->execute();
                        $result4 = $stmt->get_result();
                        $row4 = $result4->fetch_assoc();
                        array_push($data, $row4['count']);
                      }
                      if ($row['answer3'] != "") {
                        array_push($answers, $row['answer3']);
                        $stmt = $conn->prepare("SELECT COUNT(answer_id) AS count FROM answers WHERE answer = ? AND question_id = ?");
                        $stmt->bind_param('si', $row['answer3'], $question_id);
                        $stmt->execute();
                        $result4 = $stmt->get_result();
                        $row4 = $result4->fetch_assoc();
                        array_push($data, $row4['count']);
                      }
                      if ($row['answer4'] != "") {
                        array_push($answers, $row['answer4']);
                        $stmt = $conn->prepare("SELECT COUNT(answer_id) AS count FROM answers WHERE answer = ? AND question_id = ?");
                        $stmt->bind_param('si', $row['answer4'], $question_id);
                        $stmt->execute();
                        $result4 = $stmt->get_result();
                        $row4 = $result4->fetch_assoc();
                        array_push($data, $row4['count']);
                      }
                      if ($row['answer5'] != "") {
                        array_push($answers, $row['answer5']);
                        $stmt = $conn->prepare("SELECT COUNT(answer_id) AS count FROM answers WHERE answer = ? AND question_id = ?");
                        $stmt->bind_param('si', $row['answer5'], $question_id);
                        $stmt->execute();
                        $result4 = $stmt->get_result();
                        $row4 = $result4->fetch_assoc();
                        array_push($data, $row4['count']);
                      }

                      $labels="[";
                      foreach ($answers as $answer) {
                        $labels = $labels . '"' . $answer . '"' . ',';
                      }
                      $labels = rtrim($labels, ",");
                      $labels=$labels . "]";


                      $data_str = "[";
                      foreach ($data as $d) {
                        $data_str = $data_str . $d . ',';
                      }
                      $data_str = rtrim($data_str, ",");
                      $data_str=$data_str . "]";

                      ?>

                      <div class="row" >
                        <div class="col-2"></div>
                        <div class="col-8">
                          <canvas id="chart_<?php echo $question_id;?>" class="chartjs-render-monitor mx-4">

                          </canvas></div>
                          <div class="col-2"></div>
                        </div>
                        <script type="text/javascript">
                          var ctx_<?php echo $question_id;?> = document.getElementById("chart_<?php echo $question_id;?>").getContext('2d');
                          var char_<?php echo $question_id;?> = new Chart(ctx_<?php echo $question_id;?>, {
                            type : 'bar',
                            data: {
                              labels: <?php echo $labels?>,
                              datasets: [{                            
                                data: <?php echo $data_str?>,
                                backgroundColor: <?php echo $colors[$color_index]; ?>,
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
                        <?php
                      } 
                      ?>
                    </div>
                  </div>
                  <?php
                }
              } else {
                echo "<span style='color:red'>Không có khảo sát</span>";
              }            
              ?>
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
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
    if (isset($_POST['submit'])) {
      $survey_name = $_POST['survey_name'];
      $question1 = $_POST['question1'];
      $question1_type = $_POST['question1_type'];
      if (isset($_POST['answer11']))
        $answer11 = $_POST['answer11'];
      else 
        $answer11 = "";
      if (isset($_POST['answer12']))
        $answer12 = $_POST['answer12'];
      else 
        $answer12 = "";
      if (isset($_POST['answer13']))
        $answer13 = $_POST['answer13'];
      else 
        $answer13 = "";
      if (isset($_POST['answer14']))
        $answer14 = $_POST['answer14'];
      else 
        $answer14 = "";
      if (isset($_POST['answer15']))
        $answer15 = $_POST['answer15'];
      else 
        $answer15 = "";
      
      if (isset($_POST['question2']))
        $question2 = $_POST['question2'];
      else 
        $question2 = "";
      if (isset($_POST['question2_type'])) 
        $question2_type = $_POST['question2_type'];
      else 
        $question2_type = "";
      if (isset($_POST['answer21']))
        $answer21 = $_POST['answer21'];
      else 
        $answer21 = "";
      if (isset($_POST['answer22']))
        $answer22 = $_POST['answer22'];
      else 
        $answer22 = "";
      if (isset($_POST['answer23']))
        $answer23 = $_POST['answer23'];
      else 
        $answer23 = ""; 
      if (isset($_POST['answer24']))
        $answer24 = $_POST['answer24'];
      else 
        $answer24 = "";
      if (isset($_POST['answer25']))
        $answer25 = $_POST['answer25'];
      else 
        $answer25 = "";

      if (isset($_POST['question3']))
        $question3 = $_POST['question3'];
      else 
        $question3 = "";
      if (isset($_POST['question3_type'])) 
        $question3_type = $_POST['question3_type'];
      else 
        $question3_type = "";
      if (isset($_POST['answer31']))
        $answer31 = $_POST['answer31'];
      else 
        $answer31 = "";
      if (isset($_POST['answer32']))
        $answer32 = $_POST['answer32'];
      else 
        $answer32 = "";
      if (isset($_POST['answer33']))
        $answer33 = $_POST['answer33'];
      else 
        $answer33 = ""; 
      if (isset($_POST['answer34']))
        $answer34 = $_POST['answer34'];
      else 
        $answer34 = ""; 
      if (isset($_POST['answer35']))
        $answer35 = $_POST['answer35'];
      else 
        $answer35 = "";


      $stmt = $conn->prepare("DELETE FROM questions WHERE survey_name = ?");
      $stmt->bind_param("s", $survey_name);
      $stmt-> execute();


      $stmt = $conn->prepare("INSERT INTO  questions(question, question_type, survey_name, answer1, answer2, answer3, answer4, answer5) VALUES(?,?,?,?,?,?,?,?)");
      $stmt->bind_param("ssssssss", $question1, $question1_type, $survey_name, $answer11, $answer12, $answer13, $answer14, $answer15);
      $stmt->execute();

      if ($question2 != "") {
        $stmt = $conn->prepare("INSERT INTO  questions(question, question_type, survey_name, answer1, answer2, answer3, answer4, answer5) VALUES(?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssss", $question2, $question2_type, $survey_name, $answer21, $answer22, $answer23, $answer24, $answer25);
        $stmt->execute();
      }

      if ($question3 != "") {
        $stmt = $conn->prepare("INSERT INTO  questions(question, question_type, survey_name, answer1, answer2, answer3, answer4, answer5) VALUES(?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssss", $question3, $question3_type, $survey_name, $answer31, $answer32, $answer33, $answer34, $answer35);
        $stmt->execute();
      }

      $msg = "Cập nhật khảo sát thành công";
      $type = "success";
      
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
                if (isset($msg) && isset($type)) {
              ?>
                  <div class="alert alert-<?php echo $type; ?>" role="alert">
                    <?php echo $msg; ?>
                  </div>
              <?php
                }
              ?> 
              <h1 class="g-font-weight-300 g-font-size-28 g-color-black g-mb-30">Tạo khảo sát</h1>
              <!-- Form -->
              <?php
                $stmt = $conn->prepare("SELECT * FROM surveys WHERE survey_name = ?");
                $stmt->bind_param("s", $_GET['survey_name']);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $survey_name = $row['survey_name'];

                $stmt = $conn->prepare("SELECT * FROM questions WHERE survey_name = ?");
                $stmt->bind_param("s", $_GET['survey_name']);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                  $question1 = $row['question'];
                  $question1_type = $row['question_type'];
                  $answer11 = $row['answer1'];
                  $answer12 = $row['answer2'];
                  $answer13 = $row['answer3'];
                  $answer14 = $row['answer4'];
                  $answer15 = $row['answer5'];
                }
                if ($row = $result->fetch_assoc()) {
                  $question2 = $row['question'];
                  $question2_type = $row['question_type'];
                  $answer21 = $row['answer1'];
                  $answer22 = $row['answer2'];
                  $answer23 = $row['answer3'];
                  $answer24 = $row['answer4'];
                  $answer25 = $row['answer5'];
                }
                if ($row = $result->fetch_assoc()) {
                  $question3 = $row['question'];
                  $question3_type = $row['question_type'];
                  $answer31 = $row['answer1'];
                  $answer32 = $row['answer2'];
                  $answer33 = $row['answer3'];
                  $answer34 = $row['answer4'];
                  $answer35 = $row['answer5'];
                }


              ?>
              <form class="g-py-15" method="POST" action="#">
                <div class="mb-4">
                  <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Tên khảo sát: </label>
                  <input class="form-control g-color-black g-bg-white g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" name="survey_name" readonly value="<?php echo $survey_name;?>">
                </div>


                <!-- Question 1 -->
                <div class="row" id="question1">
                  <div class="col-xs-12 col-sm-6 mb-4 form-group" id = "password_holder">
                    <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Câu hỏi 1</label>
                    <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text" name="question1" id="question1" required value="<?php echo $question1;?>">
                  </div>

                  <div class="col-xs-12 col-sm-6 mb-4 form-group" id = "confirmed_password_holder">
                    <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Loại câu hỏi</label>
                    <select class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover" style="padding-top: 5px;" name="question1_type" required id="question1_type" onchange="change_question1_type()">                          
                      <option value="text" <?php if ($question1_type == "text") echo "selected";?>>Câu hỏi mở</option>
                      <option value="option"  <?php if ($question1_type == "option") echo "selected";?>>Bình chọn</option>                  
                    </select>

                    <!-- Answer container -->
                    <div id="answers1" style="display: <?php if ($question1_type == "text") echo "none"; else echo "block";?>; margin-top: 10px;">
                      <!-- Answer A -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 1:</label>                          
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer11" id="answer11" value="<?php echo $answer11;?>">
                        </div>
                      </div>
                      <!-- End Answer A -->

                      <!-- Answer B -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 2:</label>                          
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer12" id="answer12" value="<?php echo $answer12;?>">
                        </div>
                      </div>
                      <!-- End Answer B -->

                      <!-- Answer C -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 3:</label>                          
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer13" id="answer13" value="<?php echo $answer13;?>">
                        </div>
                      </div>
                      <!-- End Answer C -->

                      <!-- Answer D -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 4:</label>                          
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer14" id="answer14" value="<?php echo $answer14;?>">
                        </div>
                      </div>
                      <!-- End Answer D -->

                      <!-- Answer E -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 5:</label>                          
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer15" id="answer15" value="<?php echo $answer15;?>">
                        </div>
                      </div>
                      <!-- End Answer E -->

                    </div>
                    <!-- End Answer container -->
                  </div>                 
                </div>
                <!-- End Question 1 -->

                <!-- Question 2 -->
                <div class="row" id="question2">
                  <div class="col-xs-12 col-sm-6 mb-4 form-group" id = "password_holder">
                    <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Câu hỏi 2</label>
                    <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text" name="question2" id="question2"  value="<?php echo $question2;?>">
                  </div>

                  <div class="col-xs-12 col-sm-6 mb-4 form-group" id = "confirmed_password_holder">
                    <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Loại câu hỏi</label>
                    <select class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover" style="padding-top: 5px;" name="question2_type" required id="question2_type" onchange="change_question2_type()">     
                      <option value="text" <?php if ($question2_type == "text") echo "selected";?>>Câu hỏi mở</option>
                      <option value="option"  <?php if ($question2_type == "option") echo "selected";?>>Bình chọn</option>
                    </select>

                    <!-- Answer container -->
                    <div id="answers2" style="display: <?php if ($question2_type == "text") echo "none"; else echo "block";?>; margin-top: 10px;">
                      <!-- Answer A -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 1:</label>
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer21" id="answer21" value="<?php echo $answer21;?>">
                        </div>
                      </div>
                      <!-- End Answer A -->

                      <!-- Answer B -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 2:</label>
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer22" id="answer22" value="<?php echo $answer22;?>">
                        </div>
                      </div>
                      <!-- End Answer B -->

                      <!-- Answer C -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 3:</label>
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer23" id="answer23" value="<?php echo $answer23;?>">
                        </div>
                      </div>
                      <!-- End Answer C -->

                      <!-- Answer D -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 4:</label>
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer24" id="answer24" value="<?php echo $answer24;?>">
                        </div>
                      </div>
                      <!-- End Answer D -->

                      <!-- Answer E -->
                       <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 5:</label>
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer25" id="answer25" value="<?php echo $answer25;?>">
                        </div>
                      </div>
                      <!-- End Answer E -->

                    </div>
                    <!-- End Answer container -->
                  </div>
                </div>
                <!-- End Question 2 -->





                <!-- Question 3 -->
                <div class="row" id="question3">
                  <div class="col-xs-12 col-sm-6 mb-4 form-group" id = "password_holder">
                    <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Câu hỏi 3</label>
                    <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text" name="question3" id="question3"  value="<?php echo $question3;?>">
                  </div>

                  <div class="col-xs-12 col-sm-6 mb-4 form-group" id = "confirmed_password_holder">
                    <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Loại câu hỏi</label>
                    <select class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover" style="padding-top: 5px;" name="question3_type" required id="question3_type" onchange="change_question3_type()">     
                      <option value="text" <?php if ($question3_type == "text") echo "selected";?>>Câu hỏi mở</option>
                      <option value="option"  <?php if ($question3_type == "option") echo "selected";?>>Bình chọn</option>
                    </select>

                    <!-- Answer container -->
                    <div id="answers3" style="display: <?php if ($question3_type == "text") echo "none"; else echo "block";?>; margin-top: 10px;">
                      <!-- Answer A -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 1:</label>
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer31" id="answer31" value="<?php echo $answer31;?>">
                        </div>
                      </div>
                      <!-- End Answer A -->

                      <!-- Answer B -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 2:</label>
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer32" id="answer32" value="<?php echo $answer32;?>">
                        </div>
                      </div>
                      <!-- End Answer B -->

                      <!-- Answer C -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 3:</label>
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer33" id="answer33" value="<?php echo $answer33;?>">
                        </div>
                      </div>
                      <!-- End Answer C -->

                      <!-- Answer D -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 4:</label>
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer34" id="answer34" value="<?php echo $answer34;?>">
                        </div>
                      </div>
                      <!-- End Answer D -->

                      <!-- Answer E -->
                      <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Đáp án 5:</label>
                        </div>

                        <div class="col-xs-12 col-sm-6 mb-4 form-group" >
                          <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="text"  name="answer35" id="answer35" value="<?php echo $answer35;?>">
                        </div>
                      </div>
                      <!-- End Answer E -->

                    </div>
                    <!-- End Answer container -->
                  </div>
                </div>
                <!-- End Question 3 -->



                <div class="mb-4 text-center">
                  <input type="submit" class="btn btn-md u-btn-secondary rounded g-py-13 g-px-50" name="submit" value="Tạo">
                </div>
              </form>
              <!-- End Form -->
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
    change_question1_type = function() {
      if ($("#question1_type").val() == "option") {
        $("#answers1").show();
        $("#answer11").prop("required", true);
        $("#answer12").prop("required", true);
      } else {
        $("#answers1").hide();
        $("#answer11").prop("required", false);
        $("#answer12").prop("required", false);
      }
    }

    change_question2_type = function() {
      if ($("#question2_type").val() == "option") {
        $("#answers2").show();
        $("#answer21").prop("required", true);
        $("#answer22").prop("required", true);
      } else {
        $("#answers2").hide();
        $("#answer21").prop("required", false);
        $("#answer22").prop("required", false);
      }
    }

    change_question3_type = function() {
      if ($("#question3_type").val() == "option") {
        $("#answers3").show();
        $("#answer31").prop("required", true);
        $("#answer32").prop("required", true);
      } else {
        $("#answers3").hide();
        $("#answer31").prop("required", false);
        $("#answer32").prop("required", false);
      }
    }


  </script>


</body>

</html>
<?php 
  $conn->close(); 
?>
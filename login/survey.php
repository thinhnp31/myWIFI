<!DOCTYPE html>

<?php
session_start();
include '../includes/db_connect.php'; 
require 'vendor/autoload.php';
use Sinergi\BrowserDetector\Browser; 
use Sinergi\BrowserDetector\Os; 
use Sinergi\BrowserDetector\Device;

    //GET all parameters
if (isset($_GET['page_id'])) {
  $page_id = $_GET['page_id'];
}
if (isset($_GET['gw_sn'])) {
  $gw_sn = $_GET['gw_sn'];
}
if (isset($_GET['gw_address'])) {
  $gw_address = $_GET['gw_address'];
}
if (isset($_GET['gw_port'])) {
  $gw_port = $_GET['gw_port'];
}
if (isset($_GET['url'])) {
  $url = $_GET['url'];
}
if (isset($_GET['mac'])) {
  $mac = $_GET['mac'];
}

    //Check whether the gateway is valid or not 
$stmt = $conn->prepare("SELECT * FROM gateways WHERE gw_sn = ? ");
$stmt->bind_param('s', $gw_sn); 
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows <= 0) {
  die("Gateway is invalid!");
} 
$row = $result->fetch_assoc();
$id = $row['id'];
$gwgroup_id = $row['gwgroup_id'];

$stmt = $conn->prepare("SELECT * FROM pages WHERE page_id = ? ");
$stmt->bind_param('i', $page_id); 
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$background = $row['background'];
$logo = $row['logo'];
$connection_type = $row['connection_type'];
$welcome = $row['welcome'];
$profile_id = $row['profile_id'];
$ad_url  = $row['ad_url'];
$survey_id = $row['survey_id'];

if ($ad_url != "") 
  $url = $ad_url;

if (isset($_GET['submit'])) { //IF user click submit     
  
  if (isset($_GET['answer_1']))   
    $answer1 = $_GET['answer_1'];
  if (isset($_GET['answer_2']))   
    $answer2 = $_GET['answer_2'];
  if (isset($_GET['answer_3']))   
    $answer3 = $_GET['answer_3'];
  if (isset($_GET['answer_4']))   
    $answer4 = $_GET['answer_4'];
  if (isset($_GET['answer_5']))   
    $answer5 = $_GET['answer_5'];

  $answers1 = [];
  if (isset($_GET['answer_11']))   
    array_push($answers1, $_GET['answer_11']);
  if (isset($_GET['answer_12'])) 
    array_push($answers1, $_GET['answer_12']);
  if (isset($_GET['answer_13'])) 
    array_push($answers1, $_GET['answer_13']);
  if (isset($_GET['answer_14'])) 
    array_push($answers1, $_GET['answer_14']);
  if (isset($_GET['answer_15'])) 
    array_push($answers1, $_GET['answer_15']);

  $answers2 = [];
  if (isset($_GET['answer_21']))   
    array_push($answers2, $_GET['answer_21']);
  if (isset($_GET['answer_22'])) 
    array_push($answers2, $_GET['answer_22']);
  if (isset($_GET['answer_23'])) 
    array_push($answers2, $_GET['answer_23']);
  if (isset($_GET['answer_24'])) 
    array_push($answers2, $_GET['answer_24']);
  if (isset($_GET['answer_25'])) 
    array_push($answers2, $_GET['answer_25']);

  $answers3 = [];
  if (isset($_GET['answer_31']))   
    array_push($answers3, $_GET['answer_31']);
  if (isset($_GET['answer_32'])) 
    array_push($answers3, $_GET['answer_32']);
  if (isset($_GET['answer_33'])) 
    array_push($answers3, $_GET['answer_33']);
  if (isset($_GET['answer_34'])) 
    array_push($answers3, $_GET['answer_34']);
  if (isset($_GET['answer_35'])) 
    array_push($answers3, $_GET['answer_35']);

  $answers4 = [];
  if (isset($_GET['answer_41']))   
    array_push($answers4, $_GET['answer_41']);
  if (isset($_GET['answer_42'])) 
    array_push($answers4, $_GET['answer_42']);
  if (isset($_GET['answer_43'])) 
    array_push($answers3, $_GET['answer_43']);
  if (isset($_GET['answer_44'])) 
    array_push($answers4, $_GET['answer_44']);
  if (isset($_GET['answer_45'])) 
    array_push($answers3, $_GET['answer_45']);

  $answers5 = [];
  if (isset($_GET['answer_51']))   
    array_push($answers4, $_GET['answer_51']);
  if (isset($_GET['answer_52'])) 
    array_push($answers4, $_GET['answer_52']);
  if (isset($_GET['answer_53'])) 
    array_push($answers3, $_GET['answer_53']);
  if (isset($_GET['answer_54'])) 
    array_push($answers4, $_GET['answer_54']);
  if (isset($_GET['answer_55'])) 
    array_push($answers3, $_GET['answer_55']);

  $stmt = $conn->prepare("SELECT * FROM questions WHERE survey_id = ? ORDER BY question_id ASC");
  $stmt->bind_param("i", $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $i = 0;
  while ($row = $result->fetch_assoc()) {    
    $i = $i + 1;
    
    switch ($i) {
      case 1:
        $stmt = $conn->prepare("INSERT INTO answers(answer, question_id) VALUES(?, ?)");
        $stmt->bind_param("si", $answer1, $row['question_id']);
        $stmt->execute();
        foreach ($answers1 as $a) {
          $stmt = $conn->prepare("INSERT INTO answers(answer, question_id) VALUES(?, ?)");
          $stmt->bind_param("si", $a, $row['question_id']);
          $stmt->execute();
        }
      break;
      case 2:
        $stmt = $conn->prepare("INSERT INTO answers(answer, question_id) VALUES(?, ?)");
        $stmt->bind_param("si", $answer2, $row['question_id']);
        $stmt->execute();
        foreach ($answers2 as $a) {
          $stmt = $conn->prepare("INSERT INTO answers(answer, question_id) VALUES(?, ?)");
          $stmt->bind_param("si", $a, $row['question_id']);
          $stmt->execute();
        }
      break;
      case 3:
        $stmt = $conn->prepare("INSERT INTO answers(answer, question_id) VALUES(?, ?)");
        $stmt->bind_param("si", $answer3, $row['question_id']);
        $stmt->execute();
        foreach ($answers3 as $a) {
          $stmt = $conn->prepare("INSERT INTO answers(answer, question_id) VALUES(?, ?)");
          $stmt->bind_param("si", $a, $row['question_id']);
          $stmt->execute();
        }
      break;
      case 4:
        $stmt = $conn->prepare("INSERT INTO answers(answer, question_id) VALUES(?, ?)");
        $stmt->bind_param("si", $answer4, $row['question_id']);
        $stmt->execute();
        foreach ($answers4 as $a) {
          $stmt = $conn->prepare("INSERT INTO answers(answer, question_id) VALUES(?, ?)");
          $stmt->bind_param("si", $a, $row['question_id']);
          $stmt->execute();
        }
      break;
      case 5:
        $stmt = $conn->prepare("INSERT INTO answers(answer, question_id) VALUES(?, ?)");
        $stmt->bind_param("si", $answer5, $row['question_id']);
        $stmt->execute();
        foreach ($answers5 as $a) {
          $stmt = $conn->prepare("INSERT INTO answers(answer, question_id) VALUES(?, ?)");
          $stmt->bind_param("si", $a, $row['question_id']);
          $stmt->execute();
        }
      break;
    }
  }

  //Create a token
  $token = md5(uniqid());
  //add this device to session table
  $stmt = $conn->prepare("INSERT INTO sessions(username, mac, token, url, page_id, gwgroup_id, profile_id, gw_sn) VALUES (?,?,?,?,?,?,?,?);");
  $stmt->bind_param('ssssiiii', $username, $mac, $token, $url, $page_id, $gwgroup_id, $profile_id, $gw_sn); 
  $stmt->execute();

  $redirect_url = "http://".$gw_address.":".$gw_port."/auth/?token=".$token;

  $conn->close();

  header('Location: '.$redirect_url);
} 
?>

<html>
<head>
  <meta charset="UTF-8">
  <title>myWifi</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/myCSS.css">
  <script src="../js/jquery-3.3.1.slim.min.js" ></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/popper.min.js"></script>
</head>
<body class='bg' style="background-image: url(<?php echo "../images/background/" . $background ?>)">
  <?php 
  if (isset($alert)) {
    ?>
    <div class="alert alert-danger" role="alert">
      <?php echo $alert; ?>
    </div>
    <?php
  }
  ?>
  <div class="container">
    <div class="row">
      <div class="col">              
      </div>
      <div class="col-10 mt-3 p-5" style="background-color: white; border-radius: 5px;">
        <div style="text-align: center">
          <img src="<?php echo "../images/logo/" . $logo ?>" class="img-fluid mb-3" alt="Responsive image">
          <div class="text-center mt-4 mb-4"><h1><?php echo $welcome;?></h1></div>
          <h1>Vui lòng hoàn thành khảo sát</h1>
          <br>
          <br>
        </div>
        <form method="GET" action="#"> 
          <input type="hidden" name="gw_sn" value="<?php echo $gw_sn; ?>">
          <input type="hidden" name="gw_address" value="<?php echo $gw_address; ?>">
          <input type="hidden" name="gw_port" value="<?php echo $gw_port; ?>">
          <input type="hidden" name="url" value="<?php echo $url; ?>">
          <input type="hidden" name="mac" value="<?php echo $mac; ?>">
          <input type="hidden" name="page_id" value="<?php echo $page_id; ?>">
          <?php 
          $stmt = $conn->prepare("SELECT * FROM questions WHERE survey_id = ? ORDER BY question_id ASC");
          $stmt->bind_param("i", $survey_id);
          $stmt->execute();
          $result = $stmt->get_result();        
          $i = 0;
          while ($row = $result->fetch_assoc()) {
            $i = $i + 1;
            switch ($row['question_type']) {
              case "text":
              ?>
              <div class="form-group mt-4">
                <label><?php echo $row['question']; ?></label>
                <input type="text" class="form-control" placeholder="Nhập câu trả lời" name="answer_<?php echo $i;?>" required>
              </div>                           
              <?php
              break;
              case "option":
              ?>
              <div class="form-group mt-4">
                <label><?php echo $row['question']; ?></label>
                <div class="row mx-4">
                  <?php
                  if ($row['answer1'] != "") {
                    ?>
                    <div class="col">
                      <input class="form-check-input" type="radio" name="answer_<?php echo $i;?>" required value="<?php echo $row['answer1'];?>">
                      <label class="form-check-label"><?php echo $row['answer1'];?></label>
                    </div>
                    <?php
                  }
                  if ($row['answer2'] != "") {
                    ?>
                    <div class="col">
                      <input class="form-check-input" type="radio" name="answer_<?php echo $i;?>" required value="<?php echo $row['answer2'];?>">
                      <label class="form-check-label"><?php echo $row['answer2'];?></label>
                    </div>
                    <?php
                  }
                  if ($row['answer3'] != "") {
                    ?>
                    <div class="col">
                      <input class="form-check-input" type="radio" name="answer_<?php echo $i;?>" required value="<?php echo $row['answer3'];?>">
                      <label class="form-check-label"><?php echo $row['answer3'];?></label>
                    </div>
                    <?php
                  }
                  if ($row['answer4'] != "") {
                    ?>
                    <div class="col">
                      <input class="form-check-input" type="radio" name="answer_<?php echo $i;?>" required value="<?php echo $row['answer4'];?>">
                      <label class="form-check-label"><?php echo $row['answer4'];?></label>
                    </div>
                    <?php
                  }
                  if ($row['answer5'] != "") {
                    ?>
                    <div class="col">
                      <input class="form-check-input" type="radio" name="answer_<?php echo $i;?>" required value="<?php echo $row['answer5'];?>">
                      <label class="form-check-label"><?php echo $row['answer5'];?></label>
                    </div>
                    <?php
                  }
                  ?>
                </div>
              </div>
              <?php
              break;
              case "checkbox":
              ?>
              <div class="form-group mt-4">
                <label><?php echo $row['question']; ?></label>
                <div class="row mx-4">
                  <?php
                  if ($row['answer1'] != "") {
                    ?>
                    <div class="col">
                      <input class="form-check-input" type="checkbox" name="answer_<?php echo $i."1";?>" value="<?php echo $row['answer1'];?>">
                      <label class="form-check-label"><?php echo $row['answer1'];?></label>
                    </div>
                    <?php
                  }
                  if ($row['answer2'] != "") {
                    ?>
                    <div class="col">
                      <input class="form-check-input" type="checkbox" name="answer_<?php echo $i."2";?>" value="<?php echo $row['answer2'];?>">
                      <label class="form-check-label"><?php echo $row['answer2'];?></label>
                    </div>
                    <?php
                  }
                  if ($row['answer3'] != "") {
                    ?>
                    <div class="col">
                      <input class="form-check-input" type="checkbox" name="answer_<?php echo $i."3";?>" value="<?php echo $row['answer3'];?>">
                      <label class="form-check-label"><?php echo $row['answer3'];?></label>
                    </div>
                    <?php
                  }
                  if ($row['answer4'] != "") {
                    ?>
                    <div class="col">
                      <input class="form-check-input" type="checkbox" name="answer_<?php echo $i."4";?>" value="<?php echo $row['answer4'];?>">
                      <label class="form-check-label"><?php echo $row['answer4'];?></label>
                    </div>
                    <?php
                  }
                  if ($row['answer5'] != "") {
                    ?>
                    <div class="col">
                      <input class="form-check-input" type="checkbox" name="answer_<?php echo $i."5";?>" value="<?php echo $row['answer5'];?>">
                      <label class="form-check-label"><?php echo $row['answer5'];?></label>
                    </div>
                    <?php
                  }
                  ?>
                </div>
              </div>
              <?php
              break;
            } 
          }               
          ?>
          <div style="text-align: center">
            <button type="submit" class="btn btn-primary btn-lg" name="submit">Hoàn tất</button>   
          </div>
        </form>
      </div>
      <div class="col">
      </div>
    </div>
  </div>           
</body>
</html>

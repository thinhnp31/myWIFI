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

  if (isset($_POST['submit'])) {
    $file  = $_FILES['fileToUpload']['tmp_name'];
    $file_name  = $_FILES['fileToUpload']['name'];
    $file_extension = end((explode(".", $file_name)));

    if ($file_extension != 'csv') {
      $msg = "Vui lòng chọn file csv";
      $type = "danger";
      header("Location: local_account.php?msg=" . $msg . "&type=" . $type);
    }

    $handle = fopen($file, "r");
    $ok = true;
    while (($filesop = fgetcsv($handle, 1000, ",")) !== false) {
      $fullname = $filesop[1];
      $username = $filesop[2];
      $password = md5($username);
      $expiry_date = $filesop[3];
      if ($expiry_date == "") 
        $expiry_date = null;
      $device_limit = $_POST['device_limit'];
      if ($device_limit == "")
        $device_limit = 0;
      
      $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
      $stmt->bind_param('s', $username);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result->num_rows > 0) {
        $ok = false;
      } else {
        $stmt = $conn->prepare("INSERT INTO users(fullname, username, password, expiry_date, device_limit) VALUES(?,?,?,?,?) ");
        $stmt->bind_param('ssssi', $fullname, $username, $password, $expiry_date, $device_limit);
        $stmt->execute();
      }
    }

    if ($ok) {
      $msg = "Thêm tài khoản thành công";
      $type = "success";
    } else {
      $msg = "Có lỗi trong quá trình thêm tài khoản. Một số tài khoản có thể bị thiếu";
      $type = "danger";
    }
    
    header("Location: local_account.php?msg=" . $msg . "&type=" . $type);
  }
?>
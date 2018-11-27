<?php 
	include "includes/defines.php";
	include 'includes/db_connect.php';

	if (isset($_COOKIE['email'])) { //if this client has cookie, 
      //delete his cookie		
        $token = $_COOKIE['email'];
        $token = hash_hmac('md5', $token , salt);
        $stmt = $conn->prepare("DELETE FROM remember WHERE token = ?");
        $stmt->bind_param('s', $token); 
        $stmt->execute();
        $conn->close();
        setcookie("email", '', time() - 3600);
        setcookie("fullname", '', time() - 3600);
    }
	session_destroy();
	header("Location: " . mywifi_admin);
?>
<?php 
  $conn->close(); 
?>
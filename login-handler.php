<?php 
	//Connect to DB
	include 'includes/db_connect.php'; 

	//Get email/password from login form
	$email = $_GET['email'];
	$password = md5($_GET['password']);

	//Check whether this email/password is valid or not    
	$stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? AND password = ?;");
    $stmt->bind_param('ss', $email, $password); 
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
    	echo "login successful";
    } else {
    	$msg = "Email hoặc mật khẩu không đúng";
    	$type = "danger";
    	$redirect_url = "http://149.28.148.202/mywifi/login.php/?msg=" . $msg . "&type=" . $type;
    	$conn->close();
    	header("Location: " . $redirect_url);
    }
    $conn->close();
?>
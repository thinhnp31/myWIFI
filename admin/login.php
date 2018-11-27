<?php 
  include "includes/defines.php";  
  include 'includes/db_connect.php'; 

  //Check if this client has been logged in or not
  if (isset($_SESSION['email'])) { //if this client still has a session
    // redirect to MAIN PAGE
    $conn->close();
    header("Location: " . mywifi_admin . "index.php");
  } else {
    if (isset($_COOKIE['email'])) { //if this client has cookie, 
      //may be I can remember him (or her)
      $token = $_COOKIE['email'];
      $token = hash_hmac('md5', $token , salt);
      $stmt = $conn->prepare("SELECT * FROM remember WHERE token = ?");
      $stmt->bind_param('s', $token); 
      $stmt->execute();
      $result = $stmt->get_result();      
      if ($result->num_rows > 0) { //if his token exists in database
        //I  can remember him
        $conn->close();
        header("Location: " . mywifi_admin . "index.php");
      } 
    }
  }
  if (isset($_GET['email'])) {  //If client has submited login form
    //Get email/password from login form
    $email = $_GET['email'];
    $password = md5($_GET['password']);

    //Check whether this email/password is valid or not    
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? AND password = ?;");
    $stmt->bind_param('ss', $email, $password); 
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $_SESSION["email"] = $email;
      $_SESSION["fullname"] = $row['fullname'];

      if (isset($_GET['remember'])) {
        $token = hash_hmac('md5', $email, salt);
        $stmt = $conn->prepare("INSERT INTO remember(token) VALUES(?)");
        $stmt->bind_param('s', $token); 
        $stmt->execute();
        //cookies valid for 30 days
        setcookie("email",$email,time() + (86400 * 30), "/"); 
        setcookie("fullname",$row['fullname'],time() + (86400 * 30), "/"); 
      }

      $conn->close();
      header("Location: " . mywifi_admin . "index.php");  
    } else {
      $msg = "Email hoặc mật khẩu không đúng";
      $type = "danger";
    }
    $conn->close();
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Title -->
  <title>Login | | myWIFI</title>

  <!-- Required Meta Tags Always Come First -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <!-- Favicon -->
  <link rel="shortcut icon" href="favicon.ico">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
  <!-- CSS Global Compulsory -->
  <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="assets/vendor/icon-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/vendor/icon-line-pro/style.css">
  <link rel="stylesheet" href="assets/vendor/animate.css">

  <!-- CSS Unify -->
  <link rel="stylesheet" href="assets/css/unify-core.css">
  <link rel="stylesheet" href="assets/css/unify-components.css">
  <link rel="stylesheet" href="assets/css/unify-globals.css">

  <!-- CSS Customization -->
  <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body>
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
  <main>
    
    <!-- Login -->
    <section class="g-min-height-100vh g-flex-centered g-bg-lightblue-radialgradient-circle">      
      <div class="container g-py-100">
        <div class="row justify-content-center">
          <div class="col-sm-8 col-lg-5">
            <div class="u-shadow-v24 g-bg-white rounded g-py-40 g-px-30">
              <header class="text-center mb-4">
                <h2 class="h2 g-color-black g-font-weight-600">Đăng nhập</h2>
              </header>

              <!-- Form -->
              <form class="g-py-15" method="GET" action="#">
                <div class="mb-4">
                  <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Email:</label>
                  <input class="form-control g-color-black g-bg-white g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="email" placeholder="johndoe@gmail.com" name="email" required>
                </div>

                <div class="g-mb-35">
                  <div class="row justify-content-between">
                    <div class="col align-self-center">
                      <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Mật khẩu:</label>
                    </div>
                    <div class="col align-self-center text-right">
                      <a class="d-inline-block g-font-size-12 mb-2" href="#!">Quên mật khẩu?</a>
                    </div>
                  </div>
                  <input class="form-control g-color-black g-bg-white g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15 mb-3" type="password" placeholder="Password" name="password" required>
                  <div class="row justify-content-between">
                    <div class="col-8 align-self-center">
                      <label class="form-check-inline u-check g-color-gray-dark-v5 g-font-size-12 g-pl-25 mb-0">
                        <input class="g-hidden-xs-up g-pos-abs g-top-0 g-left-0" type="checkbox" name="remember">
                        <div class="u-check-icon-checkbox-v6 g-absolute-centered--y g-left-0">
                          <i class="fa" data-check-icon="&#xf00c"></i>
                        </div>
                        Duy trì đăng nhập
                      </label>
                    </div>
                    <div class="col-4 align-self-center text-right">
                      <button class="btn btn-md u-btn-primary rounded g-py-13 g-px-25" type="submit">Đăng nhập</button>
                    </div>
                  </div>
                </div>
              </form>
              <!-- End Form -->

              <footer class="text-center">
                <p class="g-color-gray-dark-v5 g-font-size-13 mb-0">Bạn chưa có tài khoản? <a class="g-font-weight-600" href="signup.php">Đăng ký</a>
                </p>
              </footer>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End Login -->
  </main>

  <div class="u-outer-spaces-helper"></div>


  <!-- JS Global Compulsory -->
  <script src="assets/vendor/jquery/jquery.min.js"></script>
  <script src="assets/vendor/jquery-migrate/jquery-migrate.min.js"></script>
  <script src="assets/vendor/popper.min.js"></script>
  <script src="assets/vendor/bootstrap/bootstrap.min.js"></script>


  <!-- JS Unify -->
  <script src="assets/js/hs.core.js"></script>

  <!-- JS Customization -->
  <script src="assets/js/custom.js"></script>

</body>

</html>
<?php 
  $conn->close(); 
?>
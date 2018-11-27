<?php 
  include "includes/defines.php";
  if (isset($_GET['submit'])) {

    //Connect to DB
    include 'includes/db_connect.php'; 
    
    //Get parameters from login form
    $email = $_GET['email'];
    $password = md5($_GET['password']);
    $new_password = md5($_GET['new_password']);

    //Check whether this email exists or not 
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? AND password = ?");
    $stmt->bind_param('ss', $email, $password); 
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE email = ?");
      $stmt->bind_param('ss', $new_password, $email); 
      $stmt->execute();
      $msg = "Đổi mật khẩu thành công";
      $type = "success";
    } else {
      $msg = "Mật khẩu hiện tại không đúng";
      $type = "danger";
    }
    $conn->close();   
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Title -->
  <title>Signup | | MyWIFI</title>

  <!-- Required Meta Tags Always Come First -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <!-- Favicon -->
  <link rel="shortcut icon" href="../favicon.ico">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
  <!-- CSS Global Compulsory -->
  <link rel="stylesheet" href="../assets/vendor/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/icon-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="../assets/vendor/animate.css">

  <!-- CSS Unify -->
  <link rel="stylesheet" href="../assets/css/unify-core.css">
  <link rel="stylesheet" href="../assets/css/unify-components.css">
  <link rel="stylesheet" href="../assets/css/unify-globals.css">

  <!-- CSS Customization -->
  <link rel="stylesheet" href="../assets/css/custom.css">
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
    <!-- Signup -->
    <section class="g-min-height-100vh g-flex-centered g-bg-lightblue-radialgradient-circle">
      <div class="container g-py-50">
        <div class="row justify-content-center">
          <div class="col-sm-10 col-md-9 col-lg-6">
            <div class="u-shadow-v24 g-bg-white rounded g-py-40 g-px-30">
              <header class="text-center mb-4">
                <h2 class="h2 g-color-black g-font-weight-600">Đổi mật khẩu</h2>
              </header>

              <!-- Form -->
              <form class="g-py-15" method="GET" action="#" onsubmit="return form_validate()">
                <div class="mb-4">
                  <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Họ và tên : </label>
                  <input class="form-control g-color-black g-bg-white g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" name="fullname" required readonly value="<?php echo $_SESSION['fullname']; ?>">
                </div>

                <div class="mb-4">
                  <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Email:</label>
                  <input class="form-control g-color-black g-bg-white g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="email" name="email" required readonly value="<?php echo $_SESSION['email']; ?>">
                </div>

                <div class="mb-4">
                  <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Mật khẩu hiện tại:</label>
                  <input class="form-control g-color-black g-bg-white g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="password" name="password" required>
                </div>

                <div class="row">
                  <div class="col-xs-12 col-sm-6 mb-4 form-group" id = "new_password_holder">
                      <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Mật khẩu mới:</label>
                      <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="password" placeholder="Password" name="new_password" id="new_password" required>
                  </div>

                  <div class="col-xs-12 col-sm-6 mb-4 form-group" id = "confirmed_password_holder">
                    <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Nhập lại mật khẩu:</label>
                    <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="password" placeholder="Password" id="confirmed_password">
                    <small class = "form-control-feedback" style="display:none;" id="password_helper">Mật khẩu không khớp</small>
                  </div>                  
                </div>
                <div class="mb-4" style="text-align: center">
                  <input type="submit" class="btn btn-md u-btn-primary rounded g-py-13 g-px-25" name="submit" value="Đổi mật khẩu">
                  <a class="btn btn-md u-btn-facebook rounded g-py-13 g-px-25" href="index.php">Quay lại trang chủ</a>
                </div>
              </form>
              <!-- End Form -->
              
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End Signup -->
  </main>

  <div class="u-outer-spaces-helper"></div>


  <!-- JS Global Compulsory -->
  <script src="../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../assets/vendor/jquery-migrate/jquery-migrate.min.js"></script>
  <script src="../assets/vendor/popper.min.js"></script>
  <script src="../assets/vendor/bootstrap/bootstrap.min.js"></script>


  <!-- JS Unify -->
  <script src="../assets/js/hs.core.js"></script>

  <!-- JS Customization -->
  <script src="../assets/js/custom.js"></script>

  <script type="text/javascript">
    form_validate = function()  {
      var new_password = document.getElementById("new_password").value;
      var confirmed_password = document.getElementById("confirmed_password").value;

      if (new_password == confirmed_password) {
        return true;
      } else {
        document.getElementById("new_password_holder").className = "col-xs-12 col-sm-6 mb-4 form-group u-has-error-v1";
        document.getElementById("confirmed_password_holder").className = "col-xs-12 col-sm-6 mb-4 form-group u-has-error-v1";
        document.getElementById("password_helper").style.display = "block";
        return false;
      }
    }
  </script>





</body>

</html>
<?php 
  $conn->close(); 
?>
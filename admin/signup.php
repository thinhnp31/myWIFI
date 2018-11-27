<?php 
  include "includes/defines.php";
  if (isset($_GET['email'])) {

    //Connect to DB
    include 'includes/db_connect.php'; 
    
    //Get parameters from login form
    $email = $_GET['email'];
    $password = md5($_GET['password']);
    $fullname = $_GET['fullname'];

    //Check whether this email exists or not 
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?;");
    $stmt->bind_param('s', $email); 
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      $msg = "Email đã tồn tại";
      $type = "danger";
    } else {
      $stmt = $conn->prepare("INSERT INTO admins(email, password, fullname) VALUES (?,?,?)");
      $stmt->bind_param('sss', $email, $password, $fullname); 
      $stmt->execute();
      $result = $stmt->get_result();
      $msg = "Đăng ký thành công";
      $type = "success";
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
                <h2 class="h2 g-color-black g-font-weight-600">Tạo tài khoản quản trị</h2>
              </header>

              <!-- Form -->
              <form class="g-py-15" method="GET" action="#" onsubmit="return form_validate()">
                <div class="mb-4">
                  <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Họ và tên : </label>
                  <input class="form-control g-color-black g-bg-white g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15"  placeholder="John" name="fullname" required>
                </div>

                <div class="mb-4">
                  <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Email:</label>
                  <input class="form-control g-color-black g-bg-white g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="email" placeholder="johndoe@gmail.com" name="email" required>
                </div>

                <div class="row">
                  <div class="col-xs-12 col-sm-6 mb-4 form-group" id = "password_holder">
                      <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Mật khẩu:</label>
                      <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="password" placeholder="Password" name="password" id="password" required>
                  </div>

                  <div class="col-xs-12 col-sm-6 mb-4 form-group" id = "confirmed_password_holder">
                    <label class="g-color-gray-dark-v2 g-font-weight-600 g-font-size-13">Nhập lại mật khẩu:</label>
                    <input class="form-control form-control-md g-bg-white--focus g-brd-gray-light-v4 g-brd-primary--hover rounded g-py-15 g-px-15" type="password" placeholder="Password" id="confirmed_password">
                    <small class = "form-control-feedback" style="display:none;" id="password_helper">Mật khẩu không khớp</small>
                  </div>
                </div>

                <div class="row justify-content-between mb-5">
                  <div class="col-8 align-self-center">
                    <label class="form-check-inline u-check g-color-gray-dark-v5 g-font-size-13 g-pl-25">
                      <input class="g-hidden-xs-up g-pos-abs g-top-0 g-left-0" type="checkbox" id="agree">
                      <div class="u-check-icon-checkbox-v6 g-absolute-centered--y g-left-0">
                        <i class="fa" data-check-icon="&#xf00c"></i>
                      </div>
                      Tôi đồng ý với các <a href="#!">&nbspĐiều khoản và Điều kiện</a>
                    </label>
                    <small class = "form-control-feedback" style="display:none; color:red"; id="agree_helper">Vui lòng đọc và đồng ý với các Điều khoản và Điều kiện</small>
                  </div>
                  <div class="col-4 align-self-center text-right">                    
                    <input type="submit" class="btn btn-md u-btn-primary rounded g-py-13 g-px-25" name="submit" value="Đăng ký">
                  </div>
                </div>
              </form>
              <!-- End Form -->

              <footer class="text-center">
                <p class="g-color-gray-dark-v5 g-font-size-13 mb-0">Bạn đã có tài khoản? <a class="g-font-weight-600" href="login.php">Đăng nhập</a>
                </p>
              </footer>
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
      var password = document.getElementById("password").value;
      var confirmed_password = document.getElementById("confirmed_password").value;
      var agree = document.getElementById("agree");

      if ((password == confirmed_password) && (agree.checked)) {
        return true;
      } else {
        if (password != confirmed_password) {          
          document.getElementById("password_holder").className = "col-xs-12 col-sm-6 mb-4 form-group u-has-error-v1";
          document.getElementById("confirmed_password_holder").className = "col-xs-12 col-sm-6 mb-4 form-group u-has-error-v1";
          document.getElementById("password_helper").style.display = "block";   
        } else {
          document.getElementById("password_holder").className = "col-xs-12 col-sm-6 mb-4 form-group";
          document.getElementById("confirmed_password_holder").className = "col-xs-12 col-sm-6 mb-4 form-group";
          document.getElementById("password_helper").style.display = "none";
        }
        if (!agree.checked) {
          document.getElementById("agree_helper").style.display = "block"; 
        } else {
          document.getElementById("agree_helper").style.display = "none";
        }
        return false;
      }
    }
  </script>





</body>

</html>
<?php 
  $conn->close(); 
?>
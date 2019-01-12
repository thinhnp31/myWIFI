<html>
<head>
	<title>Test Page</title>
	<meta charset="utf-8">
</head>

<style type="text/css">
.container {
	height: 100%;
	overflow: hidden;
	padding: 0;
	position: relative;
}

iframe {
	height: 56.25vw;
	left: 50%;
	min-width: 100%;
	min-height: 100%;
	transform: translate(-50%, -50%);
	position: absolute;
	top: 50%;
	width: 177.77777777778vh;
}

</style>

<body>
  <?php
  if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $app_id = "2161627994088865";
    $app_secret = "eb7b6a74283a6dc5fa3dd7412c75b1b0";
    $redirect_uri = urldecode("https://ctin.script-kiddie.net/mywifi/");
    
    //Get access token from FB
    $facebook_access_token_uri = "https://graph.facebook.com/v3.2/oauth/access_token?client_id=".$app_id."&redirect_uri=".$redirect_uri."&client_secret=".$app_secret."&code=".$code;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $facebook_access_token_uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);

    $aResponse = json_decode($response);
    $access_token = $aResponse->access_token;

    //Get user profile
    $facebook_profile_uri = "https://graph.facebook.com/me?access_token=".$access_token;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $facebook_profile_uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    
    $user = json_decode($response);
    echo $user->name."<br>";
      

  } else {
    ?>
    <a href="https://www.facebook.com/dialog/oauth?client_id=2161627994088865&redirect_uri=https://ctin.script-kiddie.net/mywifi/&scope=public_profile">Login with Facebook</a>
    <?php
  }
  ?>

  
	
</body>
</html>

<?php 
  $fbid = $graphObject->getProperty('id');         // To Get Facebook ID
  $fbfullname = $graphObject->getProperty('name'); // To Get Facebook full name
  $femail = $graphObject->getProperty('email');    // To Get Facebook email ID
  echo "Facebook ID: " . $fbid . "<br>";
  echo "Fullname: " . $fbfullname . "<br>";
  echo "Email: " . $femail . "<br>";
?>
<?php
    $servername = 'localhost';
    $username = 'mywifi';
    $password = 'ivosiyog';
    $db_name = 'mywifi';
    
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $db_name);

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
?> 
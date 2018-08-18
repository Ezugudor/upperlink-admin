<?php
     date_default_timezone_set('Africa/Lagos');
   require_once("constants.php");
   $con=new mysqli(DB_HOST, DB_USER, DB_PASS,DB_NAME) or die($con->connect_error); 
   if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
?>
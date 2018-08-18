<?php
        session_start();
		header("Content-Type:application/json");
		if((isset($_POST['uname']) && !empty($_POST['uname'])) && (isset($_POST['pwd']) && !empty($_POST['pwd']))){
				require_once("../settings/connect.php");
				require_once("../settings/config.php");
				require_once("../settings/functions.php");
				$username = mysql_prep($_POST['uname']);
				$password = md5(mysql_prep($_POST['pwd']));


				$query = $con->prepare("SELECT id from admin where username = ? and password = ? and privilege = '1' ") or die($con->error);
				$query->bind_param("ss",$username,$password)  or die($con->error);
				$query -> execute() or die($con->error);
				$query -> bind_result($userID) or die($con->error);
				$query -> store_result();
				$query -> fetch();
				if($query ->num_rows == 1){
					// record the time of login
					
					// $sessionRecord = $con->prepare("UPDATE admin set last_login = CURRENT_TIMESTAMP(),last_action = CURRENT_TIMESTAMP()  where user_id = ?") or die($con->error);
					// $sessionRecord->bind_param("s",$userID)  or die($con->error);
					// $sessionRecord -> execute() or die($con->error);

					// set all the necessary session variables
					$_SESSION['uname'] = $username;
					$_SESSION['pwd'] = $password;
					$_SESSION['user_id'] = $userID;
					// header("Content-Type:application/json");
					
						$arr = array('success' => 1);
			             echo json_encode($arr);
					
					
					// header("location:{$mainRoot}");
				}else{
					$arr = array('success' => 0);
			             echo json_encode($arr);
				}
			}
?>
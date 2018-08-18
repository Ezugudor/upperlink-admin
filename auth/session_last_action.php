<?php
		// session_start();
		// header("Content-Type:application/json");
		// require_once("../../../settings/global_config.php");
		// include_once("../settings/connect.php");
		// require_once("../settings/config.php");
		// require_once ("../settings/app_settings.php");

		$getLastAction = $con->prepare("SELECT  last_action from user_auth where user_id = ?") ;
		$getLastAction->bind_param("s",$_SESSION['user_id']);
		$getLastAction -> execute() or die($con->error);
		$getLastAction->bind_result($lastActionTime);
		$getLastAction->fetch();
		$getLastAction->close();

		if( (strtotime($lastActionTime) + $systemSessionExpires) < time() ){
			// Terminate session

				 $_SESSION=array();
				 if(isset($_COOKIE[session_name()])){
				     setcookie(session_name(),'',time()-4200,'/');
				 }
				 session_destroy();

				 // flag session timeout
			 echo "{\"success\":\"15\"}";
			 die();
		}else{
			
			$sessionRecord = $con->prepare("UPDATE user_auth set last_action = CURRENT_TIMESTAMP() where user_id = ?");
			$sessionRecord->bind_param("s",$_SESSION['user_id']);
			$sessionRecord -> execute() or die($con->error);

			echo "{\"success\":\"5\"}";
		}





		

		
?>
<?php
	header("Content-Type:application/json");
		// require_once("../settings/connect.php");
		// require_once("../settings/config.php");
		// require_once("../settings/functions.php");
		session_start();
		 $_SESSION=array();
		 if(isset($_COOKIE[session_name()])){
		     setcookie(session_name(),'',time()-4200,'/');
		     unset($_COOKIE['last_page']);
		     setcookie('last_page',' ',time()-9200,'/');
		 }
		 session_destroy();
		$g=array('success'=>1);
		echo json_encode($g);
?>
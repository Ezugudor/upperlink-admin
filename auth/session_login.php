	<?php
	   		
	if(isset($_SESSION['uname']) && isset($_SESSION['pwd'])){
				
				$username = $_SESSION['uname'];
				$password = $_SESSION['pwd'];
				$query = $con->prepare("SELECT id from admin where username = ? and password = ?") or die($con->error);
				$query->bind_param("ss",$username,$password)  or die($con->error);
				$query -> execute() or die($con->error);
				$query ->store_result();
				if($query ->num_rows == 1){
					// He is good , redirect the user
					// header("location:".ROOT.$_COOKIE['last_page']);
					header("location:".ROOT);
				}else{
					//allow the page load;
				}
				$query -> close();
			}
	 ?>
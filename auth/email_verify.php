	<?php
	   			
	if(isset($_SESSION['uname']) && isset($_SESSION['pwd'])){
				
				$usernamee = $_SESSION['uname'];
				$passwordd = $_SESSION['pwd'];
				
				$veriEmail = $con->prepare("SELECT email_verified,email_veri_code from user_auth where username = ? and password = ?") or die($con->error);
				$veriEmail->bind_param("ss",$usernamee,$passwordd)  or die($con->error);
				$veriEmail-> execute() or die($con->error);
				$veriEmail-> bind_result($emailVeriStatus,$emailVeriCode) or die($con->error);
				$veriEmail ->store_result() or die($con->error);
				$veriEmail->fetch() or die($con->error);
				// printf('<span style="font-size:100px;">%s</span>',$veriEmail->num_rows);
				if($veriEmail->num_rows == 1){
					// He is good , redirect the user
					if($emailVeriStatus != 1){
						//user not verified
						header("location:".ROOT.'verify_email');
					}
				}else{
					//allow the page load;
					
				}
				$veriEmail -> close();
			}
	 ?>
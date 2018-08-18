<?php 
session_start();
// header("Content-Type:application/json");
// require_once("../../../settings/global_config.php");
include_once("settings/connect.php");
require_once("settings/config.php");
include_once(AUTHROOT."session_others.php");
// include_once("../settings/functions.php");
// include_once("../../settings/user_info.php");

?>
<!doctype html>
<html lang="en">

<head>
	<title>Dashboard | Home</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

	<!-- VENDOR CSS -->
	<link rel="stylesheet" href="css/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/linearicons/style.css">
	<link rel="stylesheet" href="css/chartist/css/chartist-custom.css">
	
	<!-- MAIN CSS -->
	<link rel="stylesheet" href="css/main.css">

	<!-- FOR DEMO PURPOSES ONLY. You should remove this in your project -->
	<link rel="stylesheet" href="css/demo.css">

	<!-- GOOGLE FONTS -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">

	<!-- ICONS -->
	
</head>
<body>
	<!-- WRAPPER -->
	<div id="wrapper">

		<!-- NAVBAR -->
		  <?php include "includes/navbar.php"; ?>
		<!-- END NAVBAR -->

		<!-- MAIN -->
		<div class="main">

			<!-- MAIN CONTENT -->
			<div class="main-content">
				<div class="container-fluid">
		
					<div class="row">
						<div class="col-md-12">
							<!-- RECENT PURCHASES -->
							<div class="panel">
								<div class="panel-heading">
									<h3 class="panel-title">Users</h3>
									
								</div>
								<div class="panel-body no-padding">
									<table id="user-table" class="table table-striped">
										<thead>
											<tr>
												<th>id</th>
												<th>surname</th>
												<th>firstname</th>
												<th>phone</th>
												<th>email</th>
												<th>resume</th>
												<th>passport</th>
											</tr>
											<?php include "includes/users.php" ?>
										</thead>
										<tbody></tbody>
									</table>
								</div>
								
							</div>
							<!-- END RECENT PURCHASES -->
						</div>

					</div>
				
				</div>
			</div>
			<!-- END MAIN CONTENT -->
		</div>
		<!-- END MAIN -->

		<div class="clearfix"></div>
		<?php include "includes/footer.php"; ?>
	</div>
	<!-- END WRAPPER -->

	<!-- Javascript -->
	<?php include "includes/global_scripts.php"; ?>

<script>
	
$(document).ready(function(){
	    "use strict";



</script>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
</body>

</html>
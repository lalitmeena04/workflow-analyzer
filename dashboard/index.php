<?php
	// Checking if user is already logged in then redirect to home page
	require_once 'php/config.php';
	session_start();
	if(isset($_SESSION['email']) || !empty($_SESSION['email'])) 
	{
		header("location: home.php");
		exit;
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Workflow Analyzer</title>
	<link rel="icon" type="image/png" href="res/img/favicon.png" />
	<link rel="stylesheet" type="text/css" href="res/css/style.min.css">
	<link rel="stylesheet" type="text/css" href="res/css/font-awesome/all.min.css">
	<link rel="stylesheet" type="text/css" href="res/css/bootstrap/bootstrap.min.css">
	<script type="text/javascript" src="res/script/jQuery/jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="res/script/index.min.js"></script>
	<script type="text/javascript" src="res/script/bootstrap/bootstrap.min.js"></script>
	<script type="text/javascript">
		setInterval(function() 
		{
			var isMobile = detectmob();
			if (isMobile) 
			{
				//window.location("mobilesitelinkgoeshere");
				document.body.innerHTML = "<h1 align='middle'>This should redirect to mobile site if we werent lazy :P</h1>";
			}
			else if($( window ).width() < 1366 && $( window ).height() < 767)
			{
				document.body.innerHTML = "<h1 align='middle'>Please make browser window bigger for best experience and refresh page</h1>";
			}
		}, 1000); 
	</script>
</head>
<body class="main-body">
	<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
		<a class="navbar-brand" href="#">Workflow Analyzer</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav mr-auto">
			</ul>
			<div class="btn-group">
				<button class="btn btn-outline-success my-2 my-sm-0" data-toggle="modal" data-target="#loginModal"><i class="fal fa-sign-in" aria-hidden="true"></i>  Login</button>
				<button class="btn btn-outline-info my-1 my-sm-0" data-toggle="modal" data-target="#signupModal"><i class="fal fa-user-plus" aria-hidden="true"></i>  Sign up</button>
			</div>
		</div>
	</nav>
	<div class="image-container" id="top">
		<div class="scroll-image overlay"></div>
		<div id="title">
			<p data-scroll-speed="-1.65"><strong style="font-size: 60px">WORKFLOW ANALYZER</strong></p>
			<p id="title-details" data-scroll-speed="-1.65" style="font-size: 30px">Take Control Of Your Workflow</p>
		</div>
		<div id="down" onclick="window.location='#1';">
			<i class="fal fa-angle-double-down fa-2x" aria-hidden="true"></i>
		</div>
	</div>
	<div class="info-box" id="1">
		<div class="info-box-container">
			<p class="info-box-container-title">ABOUT US</p>
			<p class="info-box-container-text">We are a bunch of college students here to help you out with your projects' workflow report generation.</p>
			<p class="info-box-container-text">We aim to provide in-depoth reporting of all your active and previous projects.</p>
		</div>
		<div class="copyright-info">
			<p>Code Reapers 2018-19<br>About us | Privacy policy | Contact us</p>
		</div>
	</div>
	<div id="totop" onclick="window.location='#top';">
		<i class="fal fa-angle-double-up fa-2x"></i>
	</div>

	<div style="overflow-y: auto;" class="modal fade" id="loginModal">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">

				<!-- Modal Header -->
				<div class="modal-header">
					<h4 class="modal-title">Login</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<!-- Modal body -->
				<div class="modal-body">
					<form id="login-form">
						<label>E-Mail</label>
						<input class="form-control" type="email" name="email" placeholder="E-Mail">
						<br>
						<label>Password</label>
						<input class="form-control" type="password" name="password" placeholder="Password">
					</form>
				</div>

				<!-- Modal footer -->
				<div class="modal-footer">
					<button type="button" class="btn btn-success" id="login">Login</button>
				</div>

			</div>
		</div>
	</div>

	<div class="modal fade" id="signupModal">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">

				<!-- Modal Header -->
				<div class="modal-header">
					<h4 class="modal-title">Sign up</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<!-- Modal body -->
				<div class="modal-body">
					<form id="signup-form">
						<label>Name</label>
						<input class="form-control" type="text" name="name" placeholder="Name">
						<br>
						<label>E-Mail</label>
						<input class="form-control" type="email" name="email" placeholder="E-Mail">
						<br>
						<label>Password</label>
						<input class="form-control" type="password" name="password" placeholder="Password">
						<br>
						<label>Confirm Password</label>
						<input class="form-control" type="password" name="cnfpassword" placeholder="Confirm Password">
					</form>
				</div>

				<!-- Modal footer -->
				<div class="modal-footer">
					<button type="button" class="btn btn-info" id="signup">Sign Up</button>
				</div>

			</div>
		</div>
	</div>
	<!--scripts go below this line-->
	<script type="text/javascript">
		$('#login').click(function()
		{
			$.ajax(
			{
				url:"php/login.php",
				method:"POST",
				data:$('#login-form').serialize(),
				success:function(response) 
				{
					if(response=="success")
					{
						window.location.href="home.php";
					}
					else
					{
						alert(response);
						$("#login-form")[0].reset();
					}
				}
			});  
		});

		$('#signup').click(function()
		{            
			$.ajax(
			{
				url:"php/signup.php",
				method:"POST",
				data:$('#signup-form').serialize(),
				success:function(response) 
				{
					alert(response);
					$("#signupModal").modal("hide");
					$("#loginModal").modal("show");
				}
			});  
		});
	</script>
</body>
</html>
<body>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Daily UI - Day 1 Sign In</title>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
	<!-- Google Fonts -->
	<link href='https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700|Lato:400,100,300,700,900' rel='stylesheet' type='text/css'>

	<link rel="stylesheet" href="<?php echo base_url()."assets_login";?>/css/animate.css">
	<!-- Custom Stylesheet -->
	<link rel="stylesheet" href="<?php echo base_url()."assets_login";?>/css/style.css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
</head>

<body>
	<div class="container">
		<div class="top">
			<h1 id="title" class="hidden"><span id="logo">Login,<span>User</span></span></h1>
		</div>
		
		<div class="login-box animated fadeInUp">
		
						<form class="login100-form validate-form" action="<?php echo base_url().'index.php/login/auth'?>" method="post">
			<div class="box-header">
				<h2>Log In</h2>
			
			</div>
			<label for="username">Username</label>
			<br/>
			<input type="text" name="username" id="username">
			<br/>
			<label for="password">Password</label>
			<br/>
			<input type="password" name="password" id="password">
			<br/>
		   <?php if ($this->session->flashdata('msg')): ?>
        <script>
		
            swal({
                title: "Wrong User/Password",
                text: "Check Username/Password ",
                timer:2300,
                showConfirmButton: false,
                type: 'error',
				icon: 'error'
            });
        </script>
		<?php endif ?>
			<button type="submit">Sign In</button>
			<br/>
			</form>
			<a href="#"><p class="small">Forgot your password?</p></a>
		</div>
	</div>
</body>

<script>
	$(document).ready(function () {
    	$('#logo').addClass('animated fadeInDown');
    	$("input:text:visible:first").focus();
	});
	$('#username').focus(function() {
		$('label[for="username"]').addClass('selected');
	});
	$('#username').blur(function() {
		$('label[for="username"]').removeClass('selected');
	});
	$('#password').focus(function() {
		$('label[for="password"]').addClass('selected');
	});
	$('#password').blur(function() {
		$('label[for="password"]').removeClass('selected');
	});
</script>

</html>
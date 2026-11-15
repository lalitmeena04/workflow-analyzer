<?php
	// Checking if user is already logged in then redirect to home page
require_once 'config.php';
if(isset($_SESSION['email']) || !empty($_SESSION['email'])) 
{
	header("location: ../home.php");
	exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST")
{
	$username = $username_err = "";
	$password = $password_err = "";
	$name ="";
	$sql="select * from wf_users where username = '$username'";

	if(empty(trim($_POST["email"]))) 
	{
		$username_err = 'Please enter username.';
		echo $username_err;
	} 
	else 
	{
		$username = trim($_POST["email"]);
	}

	if(empty(trim($_POST['password']))) 
	{
		$password_err = 'Please enter your password.';
		echo $password_err;
	} 
	else 
	{
		$password = trim($_POST['password']);
	}

	if(empty($username_err) && empty($password_err)) 
	{
		$sql = "SELECT username,password,name FROM wf_user WHERE username = ?";

		if($stmt = mysqli_prepare($link, $sql)) 
		{
			mysqli_stmt_bind_param($stmt, 's', $param_username);
			$param_username = $username;
			if(mysqli_stmt_execute($stmt))
			{
				mysqli_stmt_store_result($stmt);
				if(mysqli_stmt_num_rows($stmt) == 1)
				{
					mysqli_stmt_bind_result($stmt, $username, $hashed_password,$name);
					if(mysqli_stmt_fetch($stmt))
					{
						if(password_verify($password, $hashed_password))
						{
							session_start();
							$_SESSION['email'] = $username;
							$_SESSION['name'] = $name;
							echo "success";
						}
						else
						{
							$password_err = 'The password you entered was not valid.';
							echo $password_err;
						}
					}
				}
				else
				{
					$username_err = 'No account found with that email.';
					echo $username_err;
				}
			} 
			else 
			{
				echo 'Oops! Something went wrong. Please try again later.';
			}
			mysqli_stmt_close($stmt);
		}
		else
		{
			echo "Prepare failed: (" . mysqli_errno($link) . ") " . mysqli_error($link)." ";

		}
	}
}
?>
<?php
	// Checking if user is already logged in then redirect to home page
require_once 'config.php';
session_start();
if(isset($_SESSION['email']) || !empty($_SESSION['email']))
{
	header("location: ../home.php");
	exit;
}
if($_SERVER["REQUEST_METHOD"] == "POST")
{
	$name = $username = $password = $confirm_password = "";
	$name_err = $username_err = $password_err = $confirm_password_err = "";

	if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		if(empty(trim($_POST["name"])))
		{
			$name_err = "Please enter a name.";
			echo $name_err;
		} 
		else
		{
			$name = trim($_POST["name"]);
		}
		if(empty(trim($_POST["email"])))
		{
			$username_err = "Please enter a email.";
			echo $username_err;
		}
		else
		{
			$sql = "SELECT username,password FROM wf_user WHERE username = ?";
			if($stmt = mysqli_prepare($link, $sql))
			{
				mysqli_stmt_bind_param($stmt, "s", $param_username);
				$param_username = trim($_POST["email"]);
				if(mysqli_stmt_execute($stmt))
				{
					mysqli_stmt_store_result($stmt);
					if(mysqli_stmt_num_rows($stmt) == 1)
					{
						$username_err = "This email is already taken.";
						echo $username_err;
					} 
					else
					{
						$username = trim($_POST["email"]);
					}
				}
				else
				{
					echo "Oops! Something went wrong. Please try again later.";
				}
			}
			mysqli_stmt_close($stmt);
		}
		if(empty(trim($_POST['password'])))
		{
			$password_err = "Please enter a password.";
			echo $password_err;
		}
		elseif(strlen(trim($_POST['password'])) < 6)
		{
			$password_err = "Password must have atleast 6 characters.";
			echo $password_err;
		}
		else
		{
			$password = trim($_POST['password']);
		}
		if(empty($password_err))
		{
			if(empty(trim($_POST["cnfpassword"])))
			{
				$confirm_password_err = 'Please confirm password.';
				echo $confirm_password_err;  
			}
			else
			{
				$confirm_password = trim($_POST['cnfpassword']);
				if($password != $confirm_password)
				{
					$confirm_password_err = 'Password did not match.';
					echo $confirm_password_err;
				}
			}
		}
		if(empty($username_err) && empty($password_err) && empty($confirm_password_err))
		{
			$sql = "INSERT INTO wf_user (username, password,name) VALUES (?, ?, ?)";
			if($stmt = mysqli_prepare($link, $sql))
			{
				mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_password, $param_name);
				$param_username = $username;
				$param_password = password_hash($password, PASSWORD_DEFAULT);
				$param_name = $name;
				if(mysqli_stmt_execute($stmt))
				{
					echo "success";
				}
				else
				{
					echo "Something went wrong. Please try again later";
				}
			}
			mysqli_stmt_close($stmt);
		}
	}
}
?>
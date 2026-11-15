<?php
require_once 'config.php';
session_start();
if(!isset($_SESSION['email']) || empty($_SESSION['email'])) 
{
	header("location: ../index.php");
	exit;
}
else
{
	$user = $_SESSION['name'];
}
if($_SERVER["REQUEST_METHOD"] == "POST")
{
	$name = $_POST['staffName'];
	$email = $_POST['staffEmail'];
	$flag = 1;
	$employeeData = array_combine($email, $name);
	foreach ($employeeData as $key => $value)
	{
		$sql = "INSERT INTO wf_emails (email, name, proj_name) VALUES (?, ?, 'Workflow Analyzer')";
		if($stmt = mysqli_prepare($link, $sql))
		{
			mysqli_stmt_bind_param($stmt, "ss", $key, $value);
			if(mysqli_stmt_execute($stmt))
			{
			}
			else
			{
				$flag = 0;
			}
		}
		mysqli_stmt_close($stmt);
	}
	if($flag)
	{
		echo "Staff Added Successfully";
	}   
	else
	{
		echo "Something went wrong";
	}
}
?>
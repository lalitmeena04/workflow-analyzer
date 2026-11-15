<?php

  // Login Checking: if user isnt logged in redirect to login page
require_once 'config.php';
session_start();
if(!isset($_SESSION['email']) || empty($_SESSION['email']))
{
	header("location: ../index.php");
	exit;
}
else
{
	$projMgr = $_SESSION['email'];
}
if($_SERVER["REQUEST_METHOD"] == "POST")
{
	$sql = "SELECT proj_name from wf_projects where status = 1 AND proj_mgr = '".$projMgr."'";
	$res = mysqli_query($link, $sql);
	if (mysqli_num_rows($res) > 0) 
	{
		echo "only 1 project can be active at a time";
	}
	else
	{
		$flag = 0;
		$projName = $_POST['projName'];
		$startDate = date('Y-m-d',strtotime($_POST['startDate']));
		$moduleName = $_POST['moduleName'];
		$deadline = date('Y-m-d',strtotime($_POST['deadline']));
		$moduleStartDate = $_POST['moduleStartDate'];
		$moduleDeadline = $_POST['moduleDeadline'];

		$sql = "INSERT INTO wf_projects (proj_name, start_date, deadline, proj_mgr) VALUES (?, ?, ?, ?)";
		if($stmt = mysqli_prepare($link, $sql))
		{
			if(mysqli_stmt_bind_param($stmt, "ssss", $projName, $startDate, $deadline, $projMgr))
			{
				if(mysqli_stmt_execute($stmt))
				{
					foreach ($moduleName as $index => $moduleName) 
					{
						$sql_modules = "INSERT INTO wf_modules(module, proj_name, start_date, deadline) VALUES (?, ?, ?, ?)";
						$moduleDl = date('Y-m-d',strtotime($moduleDeadline[$index]));
						$moduleSD = date('Y-m-d',strtotime($moduleStartDate[$index]));
						if($stmt_module = mysqli_prepare($link, $sql_modules))
						{
							mysqli_stmt_bind_param($stmt_module, "ssss", $moduleName, $projName, $moduleSD, $moduleDl);
							if(mysqli_stmt_execute($stmt_module))
							{
								$flag = 1; 
							}
						}
						mysqli_stmt_close($stmt_module);
					}
				}
			}
		}
		mysqli_stmt_close($stmt);

		$sql = "INSERT INTO wf_emails (email, name, proj_name) VALUES (?, ?, ?)";
		if($stmt = mysqli_prepare($link, $sql))
		{
			if(mysqli_stmt_bind_param($stmt, "sss", $projMgr, $_SESSION['name'], $projName))
			{
				if(mysqli_stmt_execute($stmt))
				{

				}
				else
				{
					$flag = 0;
				}
			}
		}
		mysqli_stmt_close($stmt);
		if($flag)
		{
			echo "success";
		}
		else
		{
			echo "Something went wrong";
		}
	}
}
?>
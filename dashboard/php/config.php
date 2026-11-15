<?php
	// Connection settings are read from the environment so that no credentials live in the repository
	define('DB_SERVER', getenv('WFA_DB_HOST') ?: 'localhost');
	define('DB_USERNAME', getenv('WFA_DB_USER') ?: 'root');
	define('DB_PASSWORD', getenv('WFA_DB_PASSWORD') ?: '');
	define('DB_NAME', getenv('WFA_DB_NAME') ?: 'workflow_analyzer');
	$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if($link == false)
	{
	    die("ERROR: Could not connect. " . mysqli_connect_error());
	}
?>
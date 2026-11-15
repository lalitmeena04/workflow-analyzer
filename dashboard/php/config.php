<?php
	// Connection settings are read from the environment so that no credentials live in the repository
	define('DB_SERVER', getenv('WFA_DB_HOST') ?: 'localhost');
	define('DB_USERNAME', getenv('WFA_DB_USER') ?: 'root');
	define('DB_PASSWORD', getenv('WFA_DB_PASSWORD') ?: '');
	define('DB_NAME', getenv('WFA_DB_NAME') ?: 'workflow_analyzer');
	define('CONSENT_URL', rtrim(getenv('WFA_CONSENT_URL') ?: 'http://localhost:5000', '/'));
	$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if($link == false)
	{
	    die("ERROR: Could not connect. " . mysqli_connect_error());
	}
	mysqli_query($link, "SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");

	function wf_query($link, $sql, $params = array())
	{
		$stmt = mysqli_prepare($link, $sql);
		if($params)
		{
			mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
		}
		mysqli_stmt_execute($stmt);
		return mysqli_stmt_get_result($stmt);
	}
?>
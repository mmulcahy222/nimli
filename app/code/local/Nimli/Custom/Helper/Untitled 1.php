<?php

class Magento_MySQL 
{
	function __construct($location, $username, $password)
	{
		mysql_connect($location,$username,$password);
	}
}

$mysql = new Magento_MySQL("localhost", "root", "");


?>
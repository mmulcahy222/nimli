<?php

class Import_MySQL
{
	private $con = NULL;
	private $database = NULL;
	
    function __construct($location, $username, $password, $database)
    {
        $this->con = mysql_connect($location, $username, $password);
        if (!$this->con)
        {
            die('Could not connect: ' . mysql_error());
        }
        mysql_select_db($database, $this->con);
    }
    
    function query($id)
    {
    	$result = mysql_query("SELECT * FROM nm_partner_tax_profile WHERE tax_profile_id = $id");
    	return $result;
   	}
   	
   	function getCategories()
   	{
   		$result = mysql_query("SELECT * FROM companyitemcategory");
   		while(mysql_fetch_assoc($result));
    	return $result;
	}
}

$mysql = new Import_MySQL("localhost", "root", "", "");
ve($mysql->query(2));
?>
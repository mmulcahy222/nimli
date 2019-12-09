<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'C:/MagentoProject' .
    PATH_SEPARATOR . 'C:/xampp/htdocs/adminfiles' . PATH_SEPARATOR .
    'C:/xampp/htdocs/chunk/');

require_once ("/app/Mage.php"); //to do Magento Library functions
Mage::app('admin');
$c = mysql_connect('localhost', 'root', '');
mysql_select_db('puck', $c);

////////////////////////////////////
//GET ALL IMAGE LINKS
////////////////////////////////////
$swatch_url_sql = "SELECT ItemCode, AttributeValue48 as swatchurl FROM itemattribute";
$swatch_url_result = mysql_query($swatch_url_sql);
while ($row = mysql_fetch_assoc($swatch_url_result))
{
	$swatch_url_array[$row['ItemCode']] = $row['swatchurl'];
}

$file_name = end(explode('/', 'http://www.nimli.com/images/10372/10372navy.gif'));

ve($swatch_url_array);


?>
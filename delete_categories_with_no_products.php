<?php
set_include_path(get_include_path() . PATH_SEPARATOR . 'C:/makeshift/files/saffron');
require_once('app/Mage.php');
Mage::app('admin');


$categories = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect("*");
foreach ($categories as $key => $category) {
	$category_id = $category->getId();
	$category_name = $category->getName();
	$category_product_count = count($category->getProductCollection());
	if($category_id > 2)
	{
		if($category_product_count == 0)
		{
			$category->delete();
			echo $category_name . ' ' . $category_id . ' '  . $category_product_count . "----- This category has been deleted\n";
		}
	}
}



?>
<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'C:/makeshift/files/saffron/');

require 'app/Mage.php';
Mage::app('admin');

$category_collection = Mage::getModel('catalog/category')->getCollection();

foreach ($category_collection as $key => $category) {
		if($category->getId <= 2) continue;
		$category->delete();
		echo $category->getName() . "has been deleted" . "\n";

}

?>
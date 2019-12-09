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
$product_sheet_sql = "SELECT id1, brand FROM brands_csv";
$product_sheet_result = mysql_query($product_sheet_sql);
while ($row = mysql_fetch_assoc($product_sheet_result))
{
	$product_sheet_array[$row['id1']] = $row['brand'];
}


////////////////////////////////////
//GET ALL ITEMS IN SKU
////////////////////////////////////
$sql = "SELECT ItemCode, ParentItemCode FROM item";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result))
{
	$sku_array[] = $row['ItemCode'];
	$parent_array[$row['ItemCode']] = $product_sheet_array[$row['ItemCode']];
	if(empty($parent_array[$row['ItemCode']])){$parent_array[$row['ItemCode']] = $product_sheet_array[$row['ItemCode']];}
}
$parent_array = array_filter($parent_array, 'strlen');

//////////////////////////////////
//DELETE PRODUCT IMAGES
//////////////////////////////////
$media_api = Mage::getModel("catalog/product_attribute_media_api");
function delete_product_images($product)
{
	$attributes = $product->getTypeInstance ()->getSetAttributes ();
				if (isset ( $attributes ['media_gallery'] )) {
					$gallery = $attributes ['media_gallery'];
					//Get the images
					$galleryData = $product->getMediaGallery ();
					foreach ( $galleryData ['images'] as $image ) {
						//if image exists
						if (
							$gallery->getBackend ()->getImage ( $product, $image ['file'] )) 
							{
							$gallery->getBackend ()->removeImage ( $product, $image ['file'] );
						}
					}
					$product->save ();
				}
				echo $product->getSku() . " has it's images deleted\n";
}








////////////////////////////////
//LOOP THROUGH PRODUCTS (DELETE AND ADD ACCORDINGLY)
/////////////////////////////////
////////////////////////////////
//LOOP THROUGH PRODUCTS (DELETE AND ADD ACCORDINGLY)
/////////////////////////////////
/*
foreach(Mage::getModel('catalog/product')->getCollection() as $product)
{
	$time = time();
	$sku = $product->getSku();
	$brand = $parent_array[$sku];
	$product->setBrand($brand);
	//$product->save();
	$seconds = time() - $time;
	echo "$sku has been given a brand $brand ($seconds seconds)\n\n";	
	Mage::app()->getCache()->clean();
	unset($product);			
}
*/

fpve($parent_array);


?>
<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'C:/MagentoProject' .
    PATH_SEPARATOR . 'C:/xampp/htdocs/adminfiles' . PATH_SEPARATOR .
    'C:/xampp/htdocs/chunk/');

require_once ("/app/Mage.php"); //to do Magento Library functions
Mage::app('admin');


$c = mysql_connect('localhost', 'root', '');
mysql_select_db('puck', $c);



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




foreach (Mage::getModel('catalog/product')->getCollection()->
    addAttributeToSelect('*')->addAttributeToFilter('brand', 'azuri') as $product)
{
    $sku = $product->getSku();
	delete_product_images($product);
    $suffix_array = array('main', 'swatch', 'alt2', 'alt3', 'alt4', 'alt5', 'alt6');
    foreach ($suffix_array as $suffix)
    {
        $filename = dirname(__file__) . '/azuri/' . $sku . "_{$suffix}.jpg";
        if (filesize($filename) != 0)
        {
         	switch ($suffix) {
		    case 'main':
		        $product->addImageToMediaGallery($filename, 'image', false, true);
		        break;
		    case 'swatch':
		        $product->addImageToMediaGallery($filename, 'thumbnail', false, true);
		        break;
		    case 'alt2':
		        $product->addImageToMediaGallery($filename, 'image', false, false);
		        break;
		    case 'alt3':
		        $product->addImageToMediaGallery($filename, 'image', false, false);
		        break;
		    case 'alt4':
		        $product->addImageToMediaGallery($filename, 'image', false, false);
		        break;
		    case 'alt5':
		        $product->addImageToMediaGallery($filename, 'image', false, false);
		        break;
		    case 'alt6':
		        $product->addImageToMediaGallery($filename, 'image', false, false);
		        break;
			}
        }
    }
    $product->save(); 
    echo "$sku now has images\n";
}

?>
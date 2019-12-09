<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'C:/MagentoProject' .
	PATH_SEPARATOR . 'C:/makeshift/files/saffron/adminfiles' . PATH_SEPARATOR .
	'C:/makeshift/files/saffron/' . PATH_SEPARATOR . '/chroot/home/pashmina/pashminamall.com/html');


require_once ("/app/Mage.php"); //to do Magento Library functions
Mage::app('admin');

//instantiate category model
$category_model = Mage::getModel('catalog/category');

//connect and choose database
$c = mysql_connect('localhost', 'root', '');
mysql_select_db('puck', $c);

//get result
$sql = "SELECT cac1.*, cac2.category as parentcategoryname
FROM companyitemcategory as cac1
LEFT JOIN companyitemcategory as cac2 ON cac1.parentcatid = cac2.catid
WHERE cac1.parentcatid NOT IN (700, 739, 886, 889, 890, 892, 893, 891, 895, 894, 896, 897, 898, 899, 1254, 1712, 1781, 2258, 2268, 2259, 2280)
AND cac1.category NOT LIKE '%>>'
ORDER BY cac1.catid";
$result = mysql_query($sql);

//iterate through table
$count = 0;
$path = 4;
while($row = mysql_fetch_assoc($result))
{
	
	$count++;
	
	//SET VARIABLES FROM MYSQL ROW
	$category_name = $row['Category'];
	$parent_category_name = $row['parentcategoryname'];
	$omx_category_id = $row['CatID'];
	$omx_parent_category_id = $row['ParentCatID'];
	
	//GET CATEGORY PATH
	if ($parent_category_name != null)
	{
		$parent_category = $category_model->loadByAttribute('omx_category_id', $omx_parent_category_id);
		if($parent_category)
		{
			try
			{
				$path = $category_model->loadByAttribute('omx_category_id', $omx_parent_category_id)->getPath();
			}
			catch(Exception $e){}
		}
	}

	else
	{
		$path = "1/2";
	}

	
	///ADD CATEGORY
	$category = Mage::getModel('catalog/category');
	$category->setStoreId(0); 
	$general['name'] = $category_name;
	$general['path'] = $path; 
	$general['description'] = $category_name;
	$general['meta_title'] = $category_name; //Page title
	$general['meta_keywords'] = $category_name;
	$general['meta_description'] = $category_name;
	$general['landing_page'] = ""; //has to be created in advance, here comes id
	$general['display_mode'] = "PRODUCTS_AND_PAGE"; //static block and the products are shown on the page
	$general['is_active'] = 1;
	$general['is_anchor'] = 0;
	$general['omx_category_id'] = $omx_category_id;  //new attribute that Mark created, to resolve issues with 
								//categories of the same name
	$general['url_key'] = $category_name;//url to be used for this category's page by magento.
	


	$category->addData($general);
	try {
		$category->save();
		echo "Success! $category_name is now a category. Id:  ".$category->getId()."\n";
	}
	catch (Exception $e){
		echo $e->getMessage();
	}
	//END ADD CATEGORY
	
	
	//var_export($row);
}


?>
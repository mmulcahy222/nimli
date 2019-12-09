<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'C:/makeshift/files/saffron');


require_once ("/app/Mage.php"); //to do Magento Library functions
Mage::app('admin');

$prodauct = Mage::getModel('catalog/product')->load(9);
//$helper_data = Mage::helper('custom/data');
$collection = Mage::getModel('catalog/product')->getCollection()->
    addAttributeToSelect('*')->addAttributeToFilter('brand', 'Trimark');
$category_model = Mage::getModel('catalog/category');
//foreach($collection as $h){echo $h->getName();}


////////////////////////////////////////
///FUNCTION FOR SETTING OPTIONS FOR A PRODUCT (LIKE ECO ATTRIBUTE OR COLOR)
////////////////////////////////////////
$options_array = array();

function get_magento_option_ids($attribute_name, $option_names)
{
    $options_array = &$GLOBALS["options_array"]; //this array grows over time
    $attribute_name = strtolower($attribute_name);
    if (!isset($options_array[$attribute_name]))
        //fill up array with new attribute & options (done to not redeclare model class every single row, which is memory intensive)

    {
        if ($attribute_name == '')
        {
            $attribute_name = 'attribute';
        } //only done for that one category with no attribute name but had values
        $attribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode('catalog_product',
            strtolower($attribute_name));
        $options = $attribute->getSource()->getAllOptions(true);
        foreach ($options as $array)
        {
            $options_array[$attribute_name][strtolower(trim($array[label]))] = $array[value];
        }
    }
    ///GET VALUES FROM ARRAY ABOVE
    foreach ($option_names as $option_name)
    {
        $values[] = $options_array[$attribute_name][strtolower(fix_name($attribute_name, $option_name))];
    }
    $string = implode(',', array_values($values));
    return $string;
}

function fix_name($attribute_name, $option_name)
{
    if ($attribute_name == 'eco_attribute')
    {
        if (substr_count($option_name, 'nottested') > 0)
        {
            return 'Not Tested On Animals';
        }
        if (substr_count($option_name, 'usa') > 0)
        {
            return 'Made In Usa';
        }
        if (substr_count($option_name, 'organic') > 0)
        {
            return 'Organic';
        }
        if (substr_count($option_name, 'sustainable') > 0)
        {
            return 'Sustainable';
        }
        if (substr_count($option_name, 'fairly') > 0)
        {
            return 'Fairly Traded';
        }
        if (substr_count($option_name, 'natural') > 0)
        {
            return 'Natural';
        }
        if (substr_count($option_name, 'handmade') > 0)
        {
            return 'Handmade';
        }
    }
    if (substr_count($option_name, 'Café') > 0)
    {
        return "Caf";
    }
    $option_name = str_replace("è", "e", $option_name);
    return $option_name;
}
//////////////////////////////////



/////////////////////
///CONNECT TO DATABASE AND MAKE QUERY
//////////////////////
$c = mysql_connect('localhost', 'root', '*');
mysql_select_db('puck', $c);
$sql = "SELECT * FROM item";
$result = mysql_query($sql);


//////////////////////////
//GET PRICES
//////////////////////////
echo "Making Array For Prices....\n";
$price_result = mysql_query("SELECT itemcode, price FROM keycodeitemprice k");
$price_array = array();
while ($price_row = mysql_fetch_row($price_result))
{
    $price_array[$price_row[0]] = $price_row[1];
}

////////////////////////////
//GET CATEGORIES
////////////////////////////
echo "Making Array For Categories....\n";
$category_collection = Mage::getModel('catalog/category')->getCollection()->
    addAttributeToSelect('omx_category_id');
$category_omx_id_array = array();
foreach ($category_collection as $category)
{
    $category_omx_id_array[strval($category->getomx_category_id())] = $category->
        getId();
}
$category_result = mysql_query("SELECT itemcode, catid FROM itemcategory");
$category_array = array();
while ($category_row = mysql_fetch_row($category_result))
{
    $magento_category_id = $category_omx_id_array[$category_row[1]];
    if ($magento_category_id != null)
    {
        $category_array[$category_row[0]] .= $category_omx_id_array[$category_row[1]] .
            ',';
    }
}

////////////////////////////////////
//GET CONFIGURABLE ATTRIBUTES
////////////////////////////////////
echo "Making Array For Attributes.....\n";
$product_entity_id = Mage::getModel('eav/config')->getEntityType('catalog_product')->
    getEntityTypeId();
$attributes_with_options = Mage::getResourceModel('eav/entity_attribute_collection')->
    setEntityTypeFilter($product_entity_id);
$attribute_array = array();
foreach ($attributes_with_options as $attribute_with_options)
{
    $attribute_array[$attribute_with_options->getName()] = $attribute_with_options->
        getId();
}


/////////////////////////////////////////////
//ATTRIBUTE SET ID's
////////////////////////////////////////
$attribute_set_collection = Mage::getResourceModel('eav/entity_attribute_set_collection');
foreach ($attribute_set_collection as $attribute_set)
{
    $attribute_set_array[$attribute_set->getAttributeSetName()] = $attribute_set->
        getId();
}
var_export($attribute_set_array);


////////////////////////////////////
//GET ATTRIBUTES FROM ITEM ATTRIBUTES BACKUP TABLE (brand, search, materialandfabric, etc)
////////////////////////////////////
echo "Making Array from Item Attribute backup table...\n";
$item_attribute_sql =
    "SELECT ItemCode, AttributeValue8 as Brand, AttributeValue7 as SearchTerm, AttributeValue16 as FulfillmentLatency,
AttributeValue18 as SmallImageUrl, AttributeValue19 as MainImageUrl, AttributeValue20 as Alternate1ImageUrl,
AttributeValue21 as Alternate2ImageUrl, AttributeValue22 as Alternate3ImageUrl, AttributeValue23 as Alternate4ImageUrl,
AttributeValue24 as Alternate5ImageUrl, AttributeValue25 as Alternate6ImageUrl,
AttributeValue42 as MaterialAndFabric, AttributeValue48 as SwatchImageUrl
FROM itemattribute";
$item_attribute_result = mysql_query($item_attribute_sql);
$item_attribute_array = array();
while ($item_attribute_row = mysql_fetch_assoc($item_attribute_result))
{
    $item_attribute_array[$item_attribute_row['ItemCode']] = $item_attribute_row;
}



////////////
///SKIPPING OVER ITEMS THAT ARE ALREADY IN THE SCARVES STORE
////////////
$existing_item_codes = Mage::getModel('catalog/product')->getCollection()->
    getColumnValues('sku');


$app = Mage::app(); 

while ($row = mysql_fetch_assoc($result))
{
	//if product already exists in magento store, skip and move on to next product
	if(in_array($row['ItemCode'], $existing_item_codes))
	{
		continue;
	}
	
	//timer
	$start = time();
	
	//variables
	$item_code = $row['ItemCode'];
	$type_id = ($row['HasChildren'] == 0) ? 'simple' : 'configurable';
	$surcharge = $row['SizeSurcharge'] + $row['ColorSurcharge'] + $row['StyleSurcharge'];
	$status = ($row['ProductStatus'] == 1) ? 1 : 2;
	$visibility = ($row['HasChildren'] == 0) ? 1 : 4;
	$visibility = (($row['HasChildren'] == 0)&&(empty($row['ParentItemCode']))) ? 4 : $visibility; 
	
	
	//fill values of product
    $product = Mage::getModel('catalog/product');
    $product->setWebsiteIds(array(1));
    $product->setSku($item_code);
    $product->setPrice($price_array[$item_code] + $surcharge);
    $product->setAttributeSetId(4);
    $product->setTypeId($type_id);
    $product->setName($row['ProductName']);
    $product->setDescription(strip_tags($row['InfoTextHTML']));
    $product->setShortDescription('Nimli');
    $product->setStatus($status);
    $product->setMetaKeyword(str_replace("|", ",", $item_attribute_array[$item_code]['SearchTerm']));
    $product->setVisibility($visibility);
    $product->setWeight($row['Weight']);
    $product->setCreatedAt($row['LaunchDate']);
    $product->setTaxClassId('2'); //2 is taxable goods
	$product->setOptionsContainer('container1');
	$product->setfulfillmentlatency($item_attribute_array[$item_code]['FulfillmentLatency']);
	$product->setmaterialandfabric(str_replace("|", ",", $item_attribute_array[$item_code]['MaterialAndFabric']));
	$product->setStockData(array('is_in_stock' => 1, 'qty' => 99999));
	$product->setbrand($item_attribute_array[$item_code]['Brand']);
	
	///eco attributes
	if(!empty($row['InfoText']))
	{
	$string = trim(str_replace(array('.gif', '|More Colors', '|More Colors,','|More Options','|More Scents','|More Sizes'), '', $row['InfoText']));
	$explode_string = explode(',', $string);
	$product->seteco_attribute(trim(get_magento_option_ids('eco_attribute', $explode_string), ','));
	}
	
	//set categories
	$category_ids = $category_array[$item_code];
	if(empty($category_ids) == false)
	{
	$product->setCategoryIds($category_ids);
	}
	
	//set configurable attributes for configurable products
    if ($type_id == 'configurable')
    {
    	$position_count = 0;
        $configurable_attribute_array = array($row['SizeLabel'], $row['ColorLabel'], $row['StyleLabel']);
        $configurable_attribute_array = array_filter($configurable_attribute_array, 'strlen');
        foreach($configurable_attribute_array as $key => $value)
        {
        	$configurable_attribute_array[$key] = ($value == 'Color') ? 'color_item' : strtolower(str_replace(array('|', '2'), '', $value));	
       	}
       	$configurable_attribute_data = array();
       	$position = 0;
        foreach($configurable_attribute_array as $value)
        {
        	$configurable_attribute_data[] = array('label' => $value, 'use_default' => '0',
    'position' => $position++, 'attribute_id' => $attribute_array[$value], 'attribute_code' =>
    $value, 'frontend_label' => $value, 'store_label' => $value);	
       	}
        $product->setCanSaveConfigurableAttributes(true);
        $product->setConfigurableAttributesData($configurable_attribute_data);
    }
	
	//give configurable attributes values, for item variations
	if(!empty($row['SizeLabel']))
	{
	$attribute_one = strtolower($row['SizeLabel']);
	$attribute_one = ($attribute_one == 'color') ? 'color_item' : $attribute_one;
	$attribute_one = str_replace(array('|', '2'), '', $attribute_one);
	$value_one = $row['SizeDescription'];
	$magento_product_data[$attribute_one] = get_magento_option_ids($attribute_one, array($value_one));
	}
	if(!empty($row['ColorLabel']))
	{
	$attribute_two = strtolower($row['ColorLabel']);
	$attribute_two = ($attribute_two == 'color') ? 'color_item' : $attribute_two;
	$attribute_two = str_replace(array('|', '2'), '', $attribute_two);
	$value_two = $row['ColorDescription'];
	$magento_product_data[$attribute_two] = get_magento_option_ids($attribute_two, array($value_two));
	}
	if(!empty($row['StyleLabel']))
	{
	$attribute_three = strtolower($row['StyleLabel']);
	$attribute_three = ($attribute_three == 'color') ? 'color_item' : $attribute_three;
	$attribute_three = str_replace(array('|', '2'), '', $attribute_three);
	$value_three = $row['StyleDescription'];
	$magento_product_data[$attribute_three] = get_magento_option_ids($attribute_three,  array($value_three));
	}
	$product->addData($magento_product_data);	


	//import images
    /*
    $images_array = array();
	$images_array[] = (($item_attribute_array[$item_code]['SwatchImageUrl']) != '') ? array('level' => 'thumbnail', 'url' => $item_attribute_array[$item_code]['SwatchImageUrl'], 'exclude' => true) : '';
	$images_array[] = (($item_attribute_array[$item_code]['MainImageUrl']) != '') ? array('level' => 'image', 'url' => $item_attribute_array[$item_code]['MainImageUrl'], 'exclude' => true) : '';
	$images_array = array_filter($images_array, 'is_array');

	foreach($images_array as $image_data)
	{
		//$product->addImageToMediaGallery($image_data['url'], array($image_data['level']), false, $image_data['exclude']); //last parameter is exclude, which should be true 
	}
    */

	//save product
	$count++;
	try
	{
    $product->save();
    $time = time() - $start;
    echo ($count) . ': ' . $item_code . " (" . $row['ProductName'] . ") is imported. (" . $time . " seconds)\n"; 
    }
    catch(Exception $e)
    {
    	echo $e->faultstring . "\n";
    	fwrite($log, "Excel Row: $item_code      This row was not inserted in Magento because of an error\n");
   	}
    //testing
    //if ($count == 1)
    //{
    //    break;
    //}
    
   	//reset for next iteration
	unset($configurable_attributes);
	unset($magento_product_data);
	unset($images_array);
	
	//clear product cache, so importing can go faster
	if ($a % 20 == 0)
    {
        $app->getCache()->clean();
        gc_collect_cycles();
    }
}

?>
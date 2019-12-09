<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'C:/MagentoProject' .
    PATH_SEPARATOR . 'C:/xampp/htdocs/adminfiles' . PATH_SEPARATOR .
    'C:/xampp/htdocs/chunk/');

require_once ("/app/Mage.php"); //to do Magento Library functions
Mage::app('admin');


$c = mysql_connect('localhost', 'root', '');
mysql_select_db('puck', $c);


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
        $values[] = $options_array[$attribute_name][strtolower(fix_name($attribute_name,
            $option_name))];
    }
    $string = implode(',', array_values($values));
    return $string;
}

function fix_name($attribute_name, $option_name)
{
    if ($attribute_name == 'ecoattribute')
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

////////////////////////////////////
//GET ALL ITEMS IN SKU
////////////////////////////////////
$eco_sql = "SELECT InfoText FROM item";
$eco_result = mysql_query($eco_sql);
while ($row = mysql_fetch_assoc($eco_result))
{
	$eco_array[] = $row['InfoText'];
}





////////////////////////////////
//LOOP THROUGH PRODUCTS (DELETE AND ADD ACCORDINGLY)
/////////////////////////////////

foreach(Mage::getModel('catalog/product')->getCollection() as $product)
{
	$time = time();
	$sku = $product->getSku();
	$id = $product->getId();
	if(!empty($eco_array[$sku]))
	{
	$string = trim(str_replace(array('.gif', '|More Colors', '|More Colors,','|More Options','|More Scents','|More Sizes'), '', $eco_array[$sku]));
	$explode_string = explode(',', $string);
	$eco_attribute_string = trim(get_magento_option_ids('ecoattribute', $explode_string), ',');
	}
	$product->setecoattribute($eco_attribute_string);
	$product->save();
	$seconds = time() - $time;
	echo "$sku has eco attributes again: $eco_attribute_string ($seconds seconds)" . "\n";
	Mage::app()->getCache()->clean();
}


?>
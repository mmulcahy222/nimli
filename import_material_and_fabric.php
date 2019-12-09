<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'C:/MagentoProject' .
    PATH_SEPARATOR . 'C:/xampp/htdocs/adminfiles' . PATH_SEPARATOR .
    'C:/xampp/htdocs/chunk/');

function cm($obj)
{
    var_export(get_class_methods($obj));
}

function ve($obj)
{
    var_export($obj);
}

function e($obj)
{
    echo ($obj);
}

function gc($obj)
{
    echo get_class($obj);
}

function fpve($obj)
{
    file_put_contents("C:/test/fp.txt", var_export($obj, 1));
}

function fpe($obj)
{
    file_put_contents("C:/test/fp.txt", $obj);
}

require_once ("/app/Mage.php"); //to do Magento Library functions
Mage::app('admin');

$product = Mage::getModel('catalog/product')->load(13);
$collection = Mage::getModel('catalog/product')->getCollection();
$category = Mage::getModel('catalog/category')->load(5);
//foreach($collection as $h){echo $h->getName() . "\n";}
$c = mysql_connect('localhost', 'root', '');
mysql_select_db('puck', $c);
$result = mysql_query('');

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
        $values[] = $options_array[$attribute_name][strtolower($option_name)];
    }
    $string = implode(',', array_values($values));
    return $string;
}

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



////////IMPORT
foreach(Mage::getModel('catalog/product')->getCollection() as $product)
{
	$sku = $product->getSku();
	$material_option_names = explode('|', $item_attribute_array[$sku]['MaterialAndFabric']);
	$material_id_string = get_magento_option_ids('materialandfabric', $material_option_names);
	//$product->setmaterialandfabric($material_id_string)->save();
	echo "$sku has materialandfabric updated with multiple select attributes ($material_id_string)\n";
	
}





?>
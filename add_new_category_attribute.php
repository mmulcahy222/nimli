<?php



set_include_path('C:/makeshift/files/saffron');
require_once ("app/Mage.php"); //to do Magento Library functions
Mage::app('admin');

$installer = new Mage_Eav_Model_Entity_Setup;
$installer->startSetup();
$installer->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'omx_category_id', array(
    'group'         => 'General Information',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'omx_category_id',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => false
));
$installer->endSetup();
?>
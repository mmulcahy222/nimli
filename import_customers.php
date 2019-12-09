<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'C:/MagentoProject' .
    PATH_SEPARATOR . 'C:/xampp/htdocs/adminfiles' . PATH_SEPARATOR .
    'C:/xampp/htdocs/chunk/');



require_once ("/app/Mage.php"); //to do Magento Library functions

$websiteId = Mage::app()->getWebsite()->getId();
$store = Mage::app()->getStore();
 
//CUSTOMER
$customer = Mage::getModel("customer/customer");
$customer->website_id = $websiteId; 
$customer->setStore($store);
$customer->firstname = "Barney";
$customer->lastname = "Plow";
$customer->email = "mister@plow.com";
$customer->password_hash = md5("misterplow");
$customer->save();
//ADDRESS
$address = Mage::getModel("customer/address");
$address->setCustomerId($customer->getId());
$address->firstname = $customer->firstname;
$address->lastname = $customer->lastname;
$address->country_id = "US"; //Country code here
$address->postcode = "25333";
$address->city = "Springfield";
$address->region = "IL";
$address->telephone = "444-222-3333";
$address->fax = "132-224-4422";
$address->company = "Green Girl";
$address->street = array("4 Everclear Terrace", "APT 44D");
$address->setIsDefaultBilling('1');
$address->setIsDefaultShipping('1');
$address->save();
echo "Customer has been inserted into Magento\n";
?>
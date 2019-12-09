<?php

class Nimli_Custom_Helper_Data extends Mage_Core_Helper_Abstract
{
	private $product_model = NULL;
	private $configurable_model = NULL;
	
	function __construct()
	{
		$this->product_model = Mage::getModel('catalog/product');
		$this->configurable_model = Mage::getModel('catalog/product_type_configurable');
	}
	
	//RETURN THE CORRECT ICONS FOR EACH PRODUCT IN THE PRODUCT LISTING PAGE (IT WILL BE RETURNED AS AN ARRAY OF ECO-ATTRIBUTES)
	function get_eco_attributes($product_id)
	{
			$product = $this->product_model->reset()->load($product_id);
			$attribute_name = 'ecoattribute';
	
			$eco_attributes = $product->getAttributeText($attribute_name);
			if(count($eco_attributes) == 1) $eco_attributes = array($eco_attributes); //put in array if there's ONE eco attribute filled
			return $eco_attributes;
	}////END GET_ECO_ATTRIBUTES

	
	////RETURN ALL STORE CATEGORIES (used in catalogsearch/advanced/form.phtml)
	public function get_store_categories()
    {
        $helper = Mage::helper('catalog/category');
        return $helper->getStoreCategories();
    }
    
    ////RETURN ALL POSSIBLE ECO ATTRIBUTES (used in catalogsearch/advanced/form.phtml)
    public function get_all_eco_attributes()
    {
    	$attribute = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter('eco_attribute')->getFirstItem();

		$options = Mage::getModel('eav/entity_attribute_source_table')->setAttribute($attribute)->getAllOptions(false) ;
		
		return $options;
   	}
   	
   	////RETURN ALL BRANDS (used in catalogsearch/advanced/form.phtml)	
	public function get_brands()
	{
		$brands = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect(array('brand', 'name'))->groupByAttribute('brand')->getColumnValues('brand');
		unset($brands[0]);
		ksort($brands);
		return $brands;
	}
	
	
    //RETURN TAX PROFILE ARRAY USING NIMLI ACCOUNT ID AS THE PARAMETER
    function get_vendor_tax_profile($nimli_account_id)
    {
        $nimli_account_id = mysql_escape_string($nimli_account_id);
        $sql = "SELECT tp.*, s.state_name, s.state_code, s.state_name, c.country_name
		FROM chunk.nm_partner_tax_profile as tp
		LEFT JOIN nm_state AS s ON tp.state_id = s.state_id
		LEFT JOIN nm_country as c ON s.country_id = c.country_id
		WHERE account_id = $nimli_account_id";
        $result = Mage::getSingleton('core/resource')->getConnection('core_read')->
            fetchAll($sql);
        return $result;
    }

    //GET SHIPPING & HANDLING COLUMNS & GIFT WRAP TAX COLUMNS, BY ACCOUNT AND STATE
    function get_shipping_status($nimli_account_id, $state_code)
    {
    	$nimli_account_id = mysql_escape_string($nimli_account_id);
    	$state_code = mysql_escape_string(strtoupper($state_code));
        $sql = "SELECT s.state_code, s.state_name, tp.shipping_taxable, tp.giftwrap_taxable, c.country_name, tp.account_id 
		FROM chunk.nm_partner_tax_profile as tp
		LEFT JOIN nm_state AS s ON tp.state_id = s.state_id
		LEFT JOIN nm_country as c ON s.country_id = c.country_id
		WHERE account_id = $nimli_account_id
	 	AND s.state_code = '$state_code'";
		$result = Mage::getSingleton('core/resource')->getConnection('core_read')->
            fetchAll($sql);
  		return end($result);
	}
	
	///GET IF THE SHIPPING PROFILE SHIPS BY WEIGHT, OR IF IT SHIPS BY BAND
	function get_shipping_type($shipping_profile_id)
	{
		$shipping_profile_id = mysql_escape_string($shipping_profile_id);
		$sql = "SELECT shipping_type FROM nm_partner_shipping_profile WHERE shipping_profile_id = $shipping_profile_id";
		$result = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);
		return end(end($result));
	}
	
	///THIS FUNCTION GIVES THE RATE FOR THE DIFFERENT SHIPPING METHODS (EXPEDITED, GROUND SHIPPING, ETC), FOR PRODUCTS THAT SHIP BY BAND
	function get_shipping_by_band($shipping_profile_id, $product_price)
	{
		$shipping_profile_id = mysql_escape_string($shipping_profile_id);
    	$product_price = mysql_escape_string($product_price);
		$sql = "SELECT bp.*, r.region_name, sl.service_level_name, sm.service_desc
			FROM nm_shipping_band_profile as bp
			LEFT JOIN nm_shipping_master as sm ON sm.shipping_master_id = bp.shipping_master_id
			LEFT JOIN nm_shipping_region as r ON sm.region_id = r.region_id
			LEFT JOIN nm_shipping_service_level as sl ON sm.service_level_id = sl.service_level_id
			WHERE bp.shipping_profile_id = $shipping_profile_id
			AND bp.from_price <= $product_price
			AND bp.to_price > $product_price
			ORDER BY bp.shipping_profile_id, bp.rate DESC";
		$result = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);
  		return $result;
	}
	
	///READS FROM SHIPPING BY WEIGHT PROFILE
	function get_shipping_by_weight($shipping_profile_id)
	{
		$shipping_profile_id = mysql_escape_string($shipping_profile_id);
		$sql = "SELECT s.shipping_profile_id, s.account_id, sm.shipping_master_id, swp.rate, swp.other_charges, swp.shipment_rate, swp.unit_of_measure,sm.service_desc, sl.service_level_id, sl.service_level_name, r.region_name 
			FROM nm_shipping_settings as s
			LEFT JOIN nm_shipping_master as sm ON sm.shipping_master_id = s.shipping_master_id
			LEFT JOIN nm_shipping_region as r ON sm.region_id = r.region_id
			LEFT JOIN nm_shipping_service_level as sl ON sm.service_level_id = sl.service_level_id
			RIGHT JOIN nm_shipping_weight_profile as swp ON sm.shipping_master_id = swp.shipping_master_id
			WHERE s.shipping_profile_id = $shipping_profile_id";	
		$result = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);
  		return $result;
	}
}
?>
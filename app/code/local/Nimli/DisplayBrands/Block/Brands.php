<?php

class Nimli_DisplayBrands_Block_Brands extends Mage_Core_Block_Template
{
	var $path = "/magento";
	
    public function getList($attribute)
    {
		$id = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($attribute)->getFirstItem()->getAttributeId();
		
		$db_values = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll("SELECT value FROM catalog_product_entity_varchar WHERE attribute_id = $id AND value != '' GROUP BY value ORDER BY value ASC"); 
		$values = array();
		foreach($db_values as $a){array_push($values, $a['value']);}
		return $values; 
    }
    
    protected function listBrand()
    {
        return $this->getList('brand');
    }    
	protected function listDesigner()
    {
        return $this->getList('account_id');
    }
    protected function listOccasion()
    {
        return $this->getList('occasion');
    }    
	protected function listRecipient()
    {
        return $this->getList('recipient');
    }
    protected function createList($param) //used in Filter
    {
        return $this->getList($param);
    }
   	function returnColors()
	{
		
		$sql = "
SELECT DISTINCT vc.value, mg.value as path
FROM catalog_product_entity_varchar as vc,
 catalog_product_entity_media_gallery_value as mgv,
 catalog_product_entity_media_gallery as mg
WHERE vc.entity_id = mg.entity_id
AND mg.value_id = mgv.value_id
AND mgv.position = 3
AND vc.attribute_id = (SELECT attribute_id FROM eav_attribute e WHERE attribute_code = 'valuex')
AND vc.value IS NOT NULL
AND vc.value != ''
GROUP BY vc.value
ORDER BY vc.value";
		// Execute the SQL and return the results in to $getData
		$data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);
		$distinct = array();
		foreach($data as $a)
		{
			$distinct[] = strtolower($a['value']);
		}
		return $distinct;
	}
    
    
}

?>
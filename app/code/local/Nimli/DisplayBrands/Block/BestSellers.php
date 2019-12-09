<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * New products block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Nimli_DisplayBrands_Block_BestSellers extends Mage_Catalog_Block_Product_Abstract
{
    protected $_productsCount = null;

    const DEFAULT_PRODUCTS_COUNT = 6;

    /**
     * Initialize block's cache
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData(array(
            //'cache_lifetime'    => 86400,
            //'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG),
        ));
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
           'CATALOG_PRODUCT_NEW',
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate(),
           $this->getProductsCount()
        );
    }


	///used in this->createMagentoProduct, makes an array of Magento ID's and category ID values, to populate arguments to create an object in Magento
    protected function populate_category_id($array_param, &$return_array)
    {
        foreach ($array_param[children] as $array)
        {
            $this->populate_category_id($array, &$return_array);
            $return_array[$array['category_id']] = $array['name'];
        }
        ksort($return_array);
    }



	///BEST SELLERS
    protected $_category_name = NULL;
    
    protected function _beforeToHtml()
    {  	
    	//Retrieve a map of Categories, and the numberical corresponding values which are necessary for the insertion of products to it's correct category
    	$category_singleton = Mage::getSingleton('Mage_Catalog_Model_Category_Api');
        $category_tree = $category_singleton->tree();
        $id_name_category_array = array();
        $this->populate_category_id($category_tree, $id_name_category_array); //$id_name_category_array IS NOW AN ARRAY OF
        $id_name_category_array = array_flip($id_name_category_array);
    	
    	$visibility = array(
		Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
		Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
                );
		
		$products = Mage::getResourceModel('reports/product_collection')->
		    addAttributeToSelect('*')->addOrderedQty()->
		    setOrder('ordered_qty', 'desc');
		
		foreach($products as $product)
		{
		    $parents = $product->loadParentProductIds()->getParentProductIds();
		    if (!isset($item[$parents[0]]))
		    {
		    	$item[$parents[0]] = 0;
		   	}
		    $item[$parents[0]] += (int)$product->ordered_qty;
		} 
		sort($item, SORT_NUMERIC, SORT_DESC);
		$collection = new Varien_Data_Collection();
		foreach($item as $item_key => $item_value)
		{
			echo $i . "\n";
		//$collection->addItem(Mage::getModel('catalog/product')->load($item_key));
		}
		
		/*
		$collection = Mage::getResourceModel('catalog/product_collection');
		$collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
		Mage::getModel('catalog/layer')->prepareProductCollection($collection);
		$collection->addStoreFilter()
            ->addAttributeToFilter('sku', array('101', '102' ,'103', '104', '105', '106', '107','108','109', '116' ,'117', '118', '119', '120'))
            ->setPageSize(12)
            ->setCurPage(1);
  		*/
  		
		echo Mage::helper('catalog/image')->init($collection->getFirstItem(), 'image', $this->getImageFile());

		//if(isset($this->_category_name))
  		//{
  		//	$category = Mage::getModel('catalog/category')->load($id_name_category_array[$this->_category_name]);
  		//	$products->addCategoryFilter($category);
  		//}

        $this->setProductCollection($collection);     
        return parent::_beforeToHtml();
    }

 	public function setCategoryName($category)
    {
        $this->_category_name = $category;
        return $this;
    }
	

    /**
     * Set how much product should be displayed at once.
     *
     * @param $count
     * @return Mage_Catalog_Block_Product_New
     */
    public function setProductsCount($count)
    {
        $this->_productsCount = $count;
        return $this;
    }

    /**
     * Get how much products should be displayed at once.
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (null === $this->_productsCount) {
            $this->_productsCount = self::DEFAULT_PRODUCTS_COUNT;
        }
        return $this->_productsCount;
    }
}

?>
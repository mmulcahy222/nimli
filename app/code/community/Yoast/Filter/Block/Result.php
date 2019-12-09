<?php

class Yoast_Filter_Block_Result extends Mage_Catalog_Block_Product_List
{
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection))
        {
            $collection = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('*');
            


            if ($this->getValue())
            {
                $value = $this->getValue();
            } else
            {
                $value = $this->getRequest()->getParam('filterValue', 0);
            }

            if ($this->getCategory())
            {
                $categoryId = $this->getCategory();
            } else
            {
                $categoryId = $this->getRequest()->getParam('filterCategory', 0);
            }

            if ($this->getAttributeName())
            {
                $attribute = $this->getAttributeName();
            } 

            //$attribute represents the term that comes AFTER product (ie localhost/saffron/product/new ($attribute would be new))

            $f_category = array('sale', 'new', 'bestsellers');
            if (!in_array($this->getAttributeName(), $f_category)) //regular filtering
            {
                $collection->addAttributeToFilter($attribute, array('like' => '%'. html_entity_decode(urldecode($value)) .'%'));
            }


            /////////////////////////////////////
            if ($attribute == 'new') ///VIEW NEW PRODUCTS
            {
            	$now = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                $one_month_ago = Mage::app()->getLocale()->date()->subMonth(1)->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                $collection->addAttributeToSelect('*')
				->addStoreFilter()
				->addAttributeToFilter(array(
				array(
				'attribute' => 'created_at', 
				'from' => $one_month_ago
				)
				)
				)				
				->addAttributeToSort('created_at', 'desc')
				->setPageSize($this->getProductsCount())
				->setCurPage(1);
            }
            if (($attribute == 'sale')) ///VIEW SPECIAL PRICE

            {
                $todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::
                    DATETIME_INTERNAL_FORMAT);
                $collection->addAttributeToSelect('*')->addAttributeToFilter('special_from_date',
                    array('date' => true, 'to' => $todayDate))->addAttributeToFilter('special_to_date',
                    array('or' => array(0 => array('date' => true, 'from' => $todayDate), 1 => array
                    ('is' => new Zend_Db_Expr('null')))), 'left')->addAttributeToSort('special_from_date',
                    'desc');
            
            }
            
            
	        #category filter
            if ((in_array($this->getAttributeName(), $f_category)) && (is_numeric($this->
                getValue())) && ($this->getAttributeName() != 'bestsellers'))
            {
                $collection->addCategoryFilter(Mage::getModel('catalog/category')->load($this->
                    getValue()), true);
            }
            ////////////////////////////////////
			////////////////////////////////////
			$configurable_product_model = Mage::getModel('catalog/product_type_configurable');
            $_filters = Mage::getSingleton('Yoast_Filter/Layer')->getState()->getFilters();
            foreach ($_filters as $_filter)
            {
                if ($_filter->getFilter()->getRequestVar() == "price")
                {
                    $arr = $_filter->getValue();
                    $max_value = $arr[0] * $arr[1];
                    $min_value = $max_value - $arr[1];

                    $collection->addAttributeToFilter($_filter->getFilter()->getRequestVar(), array
                        ('gt' => $min_value))->addAttributeToFilter($_filter->getFilter()->
                        getRequestVar(), array('lt' => $max_value));
                } else
                    if ($_filter->getFilter()->getRequestVar() == "cat")
                    {
                        $category = Mage::getModel('catalog/category')->load($_filter->getValue());
                        $collection->addCategoryFilter($category, true);
                    } else
                    {
                         $get_data = $collection->addAttributeToFilter($_filter->getFilter()->getRequestVar(),array('finset' => $_filter->getValue()))->addStoreFilter();
						foreach ($get_data as $product)
                        {	
                            $parent_ids = $configurable_product_model->getParentIdsByChild($product->getId());
                            $config_array[] = ($parent_ids == null) ? $product->getId() : $parent_ids[0];
                        }
                        $collection->addAttributeToFilter('entity_id', $config_array);
                        
                    }
            }

			if ($attribute == 'bestsellers') ///VIEW BEST SELLER LISTS
            {
				$collection->clear()->getSelect()->joinLeft(array('sfoi'=> Mage::getSingleton('core/resource')->getTableName('sales/order_item')), 'e.entity_id = sfoi.product_id', 'qty_ordered')
		->columns('SUM(sfoi.qty_ordered) AS qty')
		->group('e.entity_id')
		->order(array('qty DESC'));
			
            }


            if ($categoryId)
            {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $collection->addCategoryFilter($category, true);
            }
            
            
            
            
			Mage::getSingleton('catalog/layer')->prepareProductCollection($collection);
            $this->_productCollection = $collection;
            Mage::getSingleton('Yoast_Filter/Layer')->setProductCollection($this->
                _productCollection);
        }

        return $this->_productCollection;
    }
	
	function sort_ordered_quantity($a, $b)
	{
    return strcmp($b["ordered_quantity"], $a["ordered_quantity"]);
	}

    public function getAttributeName()
    {
        $attribute = $this->getRequest()->getParam('id', 0);
        $attribute = str_replace("-", " ", $attribute);
        return $attribute;
    }

    public function getValue()
    {
        $value = $this->getRequest()->getParam('v', 0);
        $value = str_replace("-", " ", $value);
        return $value;
    }
}

<?php
/**
 * EasyMage ProductGift
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so you can be sent a copy immediately.
 *
 * Original code copyright (c) 2006-2016 X.commerce, Inc. (http://www.magento.com)
 *
 * @package    EasyMage ProductGift
 * @author     Konstantin Dubovik
 * @contact    kodubovik@gmail.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class EasyMage_ProductGift_Model_Productgift extends Varien_Object {
    /**
     * Categories collection
     *
     * @var Mage_Catalog_Model_Category
     */
    protected $_categoriesCollection;

    /**
     * Categories options array
     *
     * @var array
     */
    protected $_categoriesSelectOptions;

    /**
     * @var Mage_Core_Helper_Abstract
     */
    protected $_helper;

    /**
     * Selected store id
     * @var int
     */
    protected $_storeId;

    /**
     * Config paths
     */
    const XML_PATH_GIFT_CONFIG = 'easymage_productgift/general/gift_config';

    /**
     * Class constructor
     */
    public function __construct() {
        $this->_helper = Mage::helper('easymage_productgift');
        $this->_storeId = Mage::helper('easymage_productgift')->getSelectedStore();
    }

    /**
     * Get session
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _getSession() {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * Get categories collection
     *
     * @return mixed
     */
    protected function _getCategoriesCollection()
    {
        if ($this->_categoriesCollection === NULL) {
            $this->_categoriesCollection = Mage::getModel('catalog/category')->getCollection()
                ->setStoreId($this-_storeId)
                ->addFieldToFilter('is_active', array('eq'=>'1'))
                ->addAttributeToSelect('*');
        }
        return $this->_categoriesCollection;
    }

    /**
     * Prepare product gift config
     *
     * @return mixed|string
     */
    protected function _prepareProductGiftConfig()
    {
        $selectedStore = $this->_helper->getSelectedStore();
        $productgiftConfig = Mage::getStoreConfig(self::XML_PATH_GIFT_CONFIG, $selectedStore);
        $productgiftConfig = trim($productgiftConfig);
        $productgiftConfig = unserialize($productgiftConfig);
        return $productgiftConfig;
    }

    /**
     * Get categories options array
     *
     * @return array
     */
    public function getCategoriesOptions()
    {
        $rootcatID = Mage::app()->getStore($this-_storeId)->getRootCategoryId();
        $collection = $this->_getCategoriesCollection();
        $options = array();
        foreach ($collection as $item) {
            if($item->getId() != $rootcatID) {
                $options [] = array('id' => $item->getId(), 'label' => $item->getName());
            }
        }
        $this->_categoriesSelectOptions = $options;
        return $this->_categoriesSelectOptions;
    }

    /**
     * Get product gift config
     *
     * @return array
     */
    public function getProductgiftConfig()
    {
        $productgiftConfig = $this->_prepareProductGiftConfig();
        if (empty($productgiftConfig)) {
            return false;
        }
        $resArr = array();
        foreach ($productgiftConfig as $key => $fields) {
            $resArr [] = array('category' => $key, 'apply-status' => $fields['apply-status'], 'gift-sku' => $fields['gift-sku']);
        }
        return $resArr;
    }

    /**
     * Set product gift config
     *
     * @param $value
     * @param $storeCode
     * @return $this
     * @throws Exception
     */
    public function setProductgiftConfig($value, $storeCode)
    {
        $config = Mage::getModel('adminhtml/system_config_backend_serialized_array');
        $config->setValue($value);
        $config->setPath(self::XML_PATH_GIFT_CONFIG);
        $config->setScope('stores');
        $storeId = Mage::app()->getStore($storeCode)->getId();
        $config->setScopeId($storeId);
        $config->save();
        Mage::getConfig()->reinit();
        return $this;
    }

    /**
     * Apply product git to category | Remove product gift from category
     *
     * @param $category
     * @param $storeCode
     * @param $apply
     * @return $this|bool
     * @throws Mage_Core_Exception
     */
    public function applyRule($category, $storeCode, $apply)
    {
        $productgiftConfig = $this->_prepareProductGiftConfig();
        if (empty($productgiftConfig)) {
            return false;
        }

        $updateConfig = array();
        foreach ($productgiftConfig as $key => $fields) {
            if($key == $category) {
                if($apply == 1) {
                    $apply_status = 1;
                } else {
                    $apply_status = 0;
                }
                $gift_sku = $fields['gift-sku'];
            } else $apply_status = $fields['apply-status'];

            if (!Mage::getModel('catalog/product')->loadByAttribute('sku', $fields['gift-sku']))
                Mage::throwException("Product gift SKU {$fields['gift-sku']} is invalid!");

            $updateConfig[$key] = array(
                'apply-status' => $apply_status,
                'gift-sku' => $fields['gift-sku'],
            );
        }

        $category = Mage::getModel('catalog/category')->load($category);
        $collection = $category->getProductCollection();
        foreach ($collection as $product)
        {
            if($apply == 0) {
                $gift_sku = null;
            }
            Mage::getModel('catalog/product_action')->updateAttributes(array($product->getData('entity_id')), array('sku_of_product_gift' => $gift_sku, 'is_product_gift_enabled' => $apply), 0);
        }
        $this->setProductgiftConfig($updateConfig, $storeCode);

        return $this;
    }
}
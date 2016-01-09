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
class EasyMage_ProductGift_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config paths
     */
    const XML_PATH_ENABLED = 'productgift_options/general/enable';
    const XML_PATH_GIFT_STRATEGY = 'productgift_options/general/gift_strategy';

    /**
     * Check if product gift enabled
     *
     * @return mixed
     */
    public function productGiftEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * Get product gift strategy
     *
     * @return mixed
     */
    public function productGiftStrategy()
    {
        return Mage::getStoreConfig(self::XML_PATH_GIFT_STRATEGY);
    }

    /**
     * Get current store
     *
     * @return mixed
     */
    public function getSelectedStore()
    {
        $storeCode = Mage::app()->getRequest()->getParam('store', 0);
        if (empty($storeCode)) {
            $defaultStoreId = Mage::app()
                ->getWebsite(true)
                ->getDefaultGroup()
                ->getDefaultStoreId();
            $store = Mage::getModel('core/store')->load($defaultStoreId);
            $storeCode = $store->getCode();
        }
        return $storeCode;
    }
}
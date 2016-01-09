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
class EasyMage_ProductGift_Block_Productgift extends Mage_Core_Block_Template
{
    /**
     * Get gift product
     *
     * @param $product
     * @return mixed
     */
    public function getGiftProduct($product) {
		$giftSku = $product->getData('sku_of_product_gift');
		return Mage::getSingleton('catalog/product')->loadByAttribute('sku', $giftSku);
	}

    /**
     * Check if gift enabled
     *
     * @param $product
     * @return bool
     */
    public function checkGiftEnabled($product) {
        if($product->getData('is_product_gift_enabled') && Mage::helper('easymage_productgift')->productGiftEnabled()) {
            return true;
        } else return false;
	}
}
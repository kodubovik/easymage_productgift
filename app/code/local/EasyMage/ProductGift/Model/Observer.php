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

class EasyMage_ProductGift_Model_Observer {

    /**
     * @var Mage_Core_Helper_Abstract
     */
    protected $_helper;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('easymage_productgift');
    }

    /**
     * Add gift to cart on add-to-cart action
     *
     * @param $observer
     * @return $this
     */
    public function addGift($observer)
	{
        if($this->_helper->productGiftEnabled()) {
            $product = $observer->getEvent()->getProduct();
            $item = $observer->getEvent()->getQuoteItem();
            if (!$product->getData('is_product_gift_enabled')) return $this;
            $cart = Mage::getSingleton('checkout/cart');
            $quote = $cart->getQuote();
            $sku_gift = $product->getData('sku_of_product_gift');

            if ($this->_helper->productGiftStrategy())
            {
                if ($item->getParentItem()) {
                    $qty_c = $item->getParentItem()->getQty();
                    $qty_a = $item->getParentItem()->getQtyToAdd();
                } else
                {
                    $qty_c = $item->getQty();
                    $qty_a = $item->getQtyToAdd();
                }
                if ($qty_c != $qty_a) {
                    foreach ($quote->getItemsCollection() as $it) {
                        $gift_attr = $it->getOptionByCode('gift_for_product_id');
                        if($gift_attr) {
                            if ($gift_attr->getValue() == $item->getProduct()->getId()) {
                                $it->setQty($qty_c);
                                $quote->save();
                                return $this;
                            }
                        }
                    }
                }
            }

            $product_model = Mage::getModel('catalog/product');
            $giftId = $product_model->getIdBySku($sku_gift);

            if($giftId) {
                $prod_gift = $product_model->load($giftId);
            } else {
                return $this;
            }

            if ($this->_helper->productGiftStrategy() == 0)
            {
                foreach ($quote->getAllItems() as $it) {
                    $prod_id = $it->getProductId();
                    $item_price = $it->getPrice();
                    if($prod_id == $giftId && $item_price == 0)
                        return $this;
                }
            }

            $cart->addProductsByIds(array($giftId));

            $option = Mage::getModel('sales/quote_item_option')
                ->setProductId($prod_gift->getId())
                ->setCode('gift_for_product_id')
                ->setProduct($prod_gift)
                ->setValue($item->getProduct()->getId());

            $gift_item = $quote->getItemByProduct($prod_gift);
            $gift_item->addOption($option)
                ->setCustomPrice(0)
                ->setOriginalCustomPrice(0)
                ->getProduct()->setIsSuperMode(true)
                ->setQty(1);
            $quote->save();
        }
		return $this;
	}

    /**
     * Update cart on update-items action
     *
     * @param $observer
     * @return $this
     */
    public function updateGiftCart($observer) {
        if($this->_helper->productGiftEnabled()
        && $this->_helper->productGiftStrategy()) {
            $cart = $observer->getEvent()->getCart();
            $cart_info = $observer->getEvent()->getInfo();
            $prev_item = null;
            foreach ($cart_info as $itemId => $itemInfo) {
                $item = $cart->getQuote()->getItemById($itemId);
                $qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false;
                $gift_attr = $item->getOptionByCode('gift_for_product_id');
                if ($gift_attr && $gift_attr->getValue()
                    && ($prev_item->getOrigData('qty') != $prev_item->getQty())) {
                    try {
                        $item->setQty($prev_item->getQty());
                    } catch (Exception $e) {
                        Mage::getSingleton('core/session')->addError('You can\'t change gift quantity: ' . $e->getMessage());
                    }
                }
                $prev_item = $item;
            }
        }
		return $this;
	}

    /**
     * Remove gift from cart after special product has been removed
     *
     * @param $observer
     * @return $this
     */
    public function deleteGift($observer)
    {
        if ($this->_helper->productGiftEnabled()) {
            $quote_item = $observer->getEvent()->getQuoteItem();
            $product = Mage::getModel('catalog/product')->load($quote_item->getProduct()->getId());
            $child_items = $quote_item->getChildren();
            if (!empty($child_items)) {
                $id_delete = current($child_items)->getProduct()->getId();
            } else {
                $id_delete = $quote_item->getProduct()->getId();
            }
            $has_gift = $product->getData('is_product_gift_enabled');
            if (!$has_gift) return $this;

            $cart = Mage::getSingleton('checkout/cart');
            $quote = $cart->getQuote();
            foreach ($quote->getItemsCollection() as $it) {
                $gift_attr = $it->getOptionByCode('gift_for_product_id');
                if ($gift_attr) {
                    if ($gift_attr->getValue() == $id_delete) {
                        $cart->removeItem($it->getItemId());
                        $quote->save();
                        return $this;
                    }

                }
            }
            return $this;
        }
    }

    /**
     * Check if gift SKU exists on product save action
     *
     * @param $observer
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function checkSKU($observer) {
		$product = $observer->getEvent()->getProduct();
		if (!$product->getData('is_product_gift_enabled')) return $this;
		$sku_gift = $product->getData('sku_of_product_gift');
		if (!Mage::getModel('catalog/product')->loadByAttribute('sku', $sku_gift)) Mage::throwException('SKU for gift is invalid!');
	}
}
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
class EasyMage_ProductGift_Adminhtml_ProductgiftController extends Mage_Adminhtml_Controller_Action {
    /**
     * Init actions
     *
     * @return EasyMage_ProductGift_Adminhtml_ProductgiftController
     */
    protected function  _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('easymage/productgift')
            ->_addBreadcrumb(
                Mage::helper('easymage_productgift')->__('EasyMage'),
                Mage::helper('easymage_productgift')->__('EasyMage')
            )
            ->_addBreadcrumb(
                Mage::helper('easymage_productgift')->__('Manage Productgift'),
                Mage::helper('easymage_productgift')->__('Manage Productgift')
            );
        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_title($this->__('EasyMage'))
            ->_title($this->__('Manage Productgift'));
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     *  Save config action
     */
    public function saveconfigAction()
    {
        try {
            $post = $this->getRequest()->getPost();
            $storeCode = Mage::helper('easymage_productgift')->getSelectedStore();
            $valuesToSave = array();
            $applyStatus = $post['apply-status'];
            $category = $post['category-select'];
            $giftSku = $post['gift-sku'];
            foreach ($category as $key => $val) {
                if (!Mage::getModel('catalog/product')->loadByAttribute('sku', $giftSku[$key])) {
                    $this->_redirectReferer();
                    Mage::throwException("Product gift SKU {$giftSku[$key]} is invalid!");
                }
                $valuesToSave[$val] = array(
                    'apply-status' => $applyStatus[$key],
                    'gift-sku' => $giftSku[$key],
                );
            }
            Mage::getModel('easymage_productgift/productgift')->setProductgiftConfig($valuesToSave, $storeCode);
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('easymage_productgift')->__('Product gift config has been saved.')
            );
            $this->_redirectReferer();
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    /**
     * Apply product gift | remove product gift action
     */
    public  function applyruleAction() {
        try {
            $apply = $this->getRequest()->getParam('apply');
            $category = $this->getRequest()->getParam('cat');
            $storeCode = Mage::helper('easymage_productgift')->getSelectedStore();
            Mage::getModel('easymage_productgift/productgift')->applyRule($category, $storeCode, $apply);
            if ($apply == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('easymage_productgift')->__("Product gift for category {$category} has been successfully applied.")
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('easymage_productgift')->__("Product gift for category {$category} has been successfully removed.")
                );
            }
        } catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }
}
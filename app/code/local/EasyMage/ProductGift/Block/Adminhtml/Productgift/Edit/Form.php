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
class EasyMage_ProductGift_Block_Adminhtml_Productgift_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/saveconfig', array('store' => Mage::helper('easymage_productgift')->getSelectedStore(), 'isAjax' => false)),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );
        $this->setForm($form);
        $this->setTemplate('easymage/productgift/form.phtml');

        return parent::_prepareForm();
    }

    /**
     * Render select html fields for product gift configuration
     *
     * @param int $nameIndex
     * @param string $selectedVal
     * @return string
     */
    public function getSelectHtml($nameIndex = 0, $selectedVal = '')
    {
        $retStr = '<select class="required-entry select select-category" name="category-select[' . $nameIndex . ']">';
        $retStr .= $this->getOptionsHtml($selectedVal);
        $retStr .= '</select>';
        return $retStr;
    }

    /**
     * Render options html fields for select fields
     *
     * @param string $selectedVal
     * @return string
     */
    public function getOptionsHtml($selectedVal = '')
    {
        $options = Mage::getModel('easymage_productgift/productgift')->getCategoriesOptions();
        $retStr = '<option value=""></option>';
        foreach ($options as $option) {
            if ($option['id'] == $selectedVal) {
                $retStr .= '<option value="' . addslashes($option['id']) . '" selected>' . addslashes($option['label']) . '</option>';
            } else {
                $retStr .= '<option value="' . addslashes($option['id']) . '">' . addslashes($option['label']) . '</option>';
            }
        }
        return $retStr;
    }

    /**
     * Render table body for gift config table
     *
     * @return string
     */
    public function getTableBodyHtml()
    {
        $data = Mage::getModel('easymage_productgift/productgift')->getProductgiftConfig();
        $index = 0;
        $resStr = '';
        if($data) {
            foreach ($data as $val) {
                if (!isset($val['category']) || !isset($val['gift-sku'])) {
                    continue;
                }
                $resStr .= '<tr id="table-row-' . $index . '" class="table-row-productgift">';
                $resStr .= '<td style="display:none;"><input type="hidden" name="apply-status[' . $index . ']" value="' . $val['apply-status'] . '"/></td>';
                if($val['apply-status'] == 1) {
                    $resStr .= '<td style="position:relative">' . $this->getSelectHtml($index, $val['category']) . '<span class="disabled"></span></td>';
                    $resStr .= '<td style="position:relative"><input style="width:50%;" class="required-entry" name="gift-sku[' . $index . ']" value="' . $val['gift-sku'] . '"/><span class="disabled"></span></td>';
                } else {
                    $resStr .= '<td>' . $this->getSelectHtml($index, $val['category']) . '</td>';
                    $resStr .= '<td><input style="width:50%;" class="required-entry" name="gift-sku[' . $index . ']" value="' . $val['gift-sku'] . '"/></td>';
                }
                if($index > 0 && $val['apply-status'] == 0) {
                    $resStr .= '<td>' . $this->getRemoveRowButtonHtml($index) . '</td>';
                } else {
                    $resStr .= '<td>&nbsp;</td>';
                }
                $resStr .= '<td>' . $this->getApplyRuleButtonHtml($val['apply-status'], $val['category']) . '</td>';
                $resStr .= '<td>' . $this->getRemoveRuleButtonHtml($val['apply-status'], $val['category']) . '</td>';
                $resStr .= '</tr>';
                $index++;
            }
        } else {
            $resStr .= '<tr id="table-row-' . $index . '" class="table-row-productgift">';
            $resStr .= '<td style="display:none;"><input type="hidden" name="apply-status[' . $index . ']" value="0"/></td>';
            $resStr .= '<td><select class="required-entry select select-category" name="category-select[' . $index . ']">' . $this->getOptionsHtml() . '</select></td>';
            $resStr .= '<td><input style="width:50%;" class="required-entry" name="gift-sku[' . $index . ']" value=""/></td>';
            if($index > 0) {
                $resStr .= '<td>' . $this->getRemoveRowButtonHtml($index) . '</td>';
            } else {
                $resStr .= '<td>&nbsp;</td>';
            }
            $resStr .= '</tr>';
        }

        return $resStr;
    }

    /**
     * Render remove row button
     *
     * @param int $nameIndex
     * @return string
     */
    protected function getRemoveRowButtonHtml($nameIndex = 0)
    {
        return '<button onclick="removeRow(' . $nameIndex . '); " class="delete delete-option">' . Mage::helper('easymage_productgift')->__('Remove Row') . '</button>';
    }

    /**
     * Render apply gift button
     *
     * @param int $status
     * @param $category
     * @return string
     */
    protected function getApplyRuleButtonHtml($status = 0, $category)
    {
        if($status == 0) {
            return '<a class="form-button save" href = \'' . $this->getApplyRuleUrl() . 'apply/1/cat/' . $category . '\'">' . Mage::helper('easymage_productgift')->__('Apply Rule') . '</a>';
        }
    }

    /**
     * Render remove gift button
     *
     * @param int $status
     * @param $category
     * @return string
     */
    protected function getRemoveRuleButtonHtml($status = 0, $category)
    {
        if($status == 1) {
            return '<a class="form-button delete delete-option" href = \'' . $this->getApplyRuleUrl() . 'apply/0/cat/' . $category . '\'">' . Mage::helper('easymage_productgift')->__('Remove Rule') . '</a>';
        }
    }

    /**
     * Get save gift config URL
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return Mage::helper("adminhtml")->getUrl('adminhtml/productgift/saveconfig');
    }

    /**
     * Get apply | remove gift URL
     *
     * @return mixed
     */
    public function getApplyRuleUrl()
    {
        return Mage::helper("adminhtml")->getUrl('adminhtml/productgift/applyrule');
    }
}
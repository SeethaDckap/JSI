<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Deliveryaddress\Grid\Renderer;


/**
 * Column Renderer for deliveryaddress Select Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Select extends \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    protected $listsFrontendRestrictedHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;  

    public function __construct(
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->registry = $registry;
        $this->url = $url;
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        if ($this->getColumn()->getLinks() == true) {

            $helper = $this->listsFrontendRestrictedHelper;
            /* @var $helper Epicor_Lists_Helper_Frontend_Restricted */

            $html = '';

            if ($this->isSelectedAddress($row)) {
                $html .= __('Currently Selected');
                foreach ($actions as $action) {
                    if (is_array($action)) {
                        if (($html != '') && ($action['caption'] != 'Select')) {
                            $html .= '<span class="action-divider">' . ($this->getColumn()->getDivider() ?: ' | ') . '</span>';
                            $html .= $this->_toLinkHtml($action, $row);
                        }
                    }
                }
            } else {
                foreach ($actions as $action) {
                    if (is_array($action)) {
                        if ($html != '') {
                            $html .= '<span class="action-divider">' . ($this->getColumn()->getDivider() ?: ' | ') . '</span>';
                        }
                        $html .= '<a id="link" onclick="'.$action['onclick'].'" href="'.$row->getData($this->getColumn()->getIndex()).'">'.$action['caption']->getText().'</a>';
                    }
                }
            }

            $ajaxDeliveryAddressUrl = $this->url->getUrl('epicor_lists/lists/changeshippingaddress', $helper->issecure());
            $cartPopupurl = $this->url->getUrl('epicor_lists/lists/cartpopup', $helper->issecure());
            $selectAddress = $this->url->getUrl('epicor_lists/lists/selectaddressajax', $helper->issecure());
            $html .= '<input type="hidden" name="ajaxdeliveryaddressurl" id="ajaxdeliveryaddressurl" value="' . $ajaxDeliveryAddressUrl . '">';
            $html .= '<input type="hidden" name="ajaxcode" id="ajaxcode" value="' . $row->getEntityId() . '">';
            $html .= '<input type="hidden" name="cartpopupurl" id="cartpopupurl" value="' . $cartPopupurl . '">';
            $html .= '<input type="hidden" name="selectbranch" id="selectaddress" value="' . $selectAddress . '">';
            return $html;
        } else {
            return parent::render($row);
        }
    }

    /**
     * Determines if this row is the currently selected address
     * 
     * @param \Epicor\Comm\Model\Customer\Address $row
     * 
     * @return boolean
     */
    protected function isSelectedAddress($row)
    {
        $helper = $this->listsFrontendRestrictedHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Restricted */
        $matchValue = $this->getRestrictionAddressMatchValue();

        if ($helper->isMasquerading()) {
            $addressValue = $row->getEccErpAddressCode();
        } else {
            $addressValue = $row->getEntityId();
        }
        
        return $matchValue == $addressValue;
    }

    /**
     * Gets the value to check for the restriction address
     * 
     * @return mixed (string/int)
     */
    protected function getRestrictionAddressMatchValue()
    {
        $matchVal = $this->registry->registry('ecc_select_restrction_address_val');

        if (is_null($matchVal) == false) {
            return $matchVal;
        }

        $helper = $this->listsFrontendRestrictedHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Restricted */
        $address = $helper->getRestrictionAddress();
        if ($helper->isMasquerading()) {
            $matchVal = $address->getEccErpAddressCode();
        } else {
            if ($address instanceof \Magento\Quote\Model\Quote\Address) {
                $matchVal = $address->getCustomerAddressId();
            } else {
                $matchVal = $address->getId();
            }
        }

        $this->registry->unregister('ecc_select_restrction_address_val');
        $this->registry->register('ecc_select_restrction_address_val', $matchVal);
        return $matchVal;
    }

}

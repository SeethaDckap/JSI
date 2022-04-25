<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer;

/**
 * Filesize grid column renderer. renders a file size in human readable format
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Checkboxgrid extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

   protected $_erp_customer;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }


    /**
     * 
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getErpCustomer()
    {
        if (!$this->_erp_customer) {
            $this->_erp_customer = $this->registry->registry('customer_erp_account');
        }
        return $this->_erp_customer;
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
               $html = '';
        $erpAccount = $this->getErpCustomer();
        $shipCodesMapped = unserialize($erpAccount->getAllowedShipstatusMethods());
        $allowcheck = array();
        $allowed = ($shipCodesMapped) ? $shipCodesMapped : array();
        foreach ($allowed as $allow) {
            $allowcheck[] = $allow;
        }
        $is_default = $row->getIsDefault();
        $checked = ($is_default) ? 'checked' : '';
        $search = in_array($row->getShippingStatusCode(), $allowcheck);
        if ($search && $row->getIsDefault() != 1) {
            $check = 'checked';
            $html .= '<input type="checkbox" name=links[] value=' . "'" . $row->getShippingStatusCode() . "'" . "checked=" . $check . " class='checked'></input>";
            return $html;
        } elseif ($row->getIsDefault() == 1) {
            $html .= '<input name=links[] type="checkbox" disabled readonly value=' . "'" . $row->getShippingStatusCode() . "'" . "checked=" . $checked . " class='checked'></input>";
            return $html;
        } else {
            if ($is_default) {
                $html .= '<input name=links[] type="checkbox" disabled readonly value=' . "'" . $row->getShippingStatusCode() . "'" . "checked=" . $checked . " class='checked'></input>";
                return $html;
            } else {
                $html .= '<input name=links[] type="checkbox" class="checked" value=' . "'" . $row->getShippingStatusCode() . "'" . "></input>";
                return $html;
            }
        }
    }

}

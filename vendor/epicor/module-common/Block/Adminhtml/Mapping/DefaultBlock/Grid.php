<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock;


class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = [])
    {

        parent::__construct($context, $data);

        $this->setTemplate('Epicor_Common::epicor_common/mapping/grid.phtml');
    }
    public function isSingleStoreMode()
    {
        //M1 > M2 Translation Begin (Rule P2-6.8)
        //if (!Mage::app()->isSingleStoreMode()) {
        if (!$this->_storeManager->isSingleStoreMode()) {
            //M1 > M2 Translation End
            return false;
        }
        return true;
    }

    public function getStoreSwitcherHtml()
    {
        $block = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher', 'store_switcher')->setUseConfirm(false);
        $this->setChild('store_switcher', $block);
        return $this->getChildHtml('store_switcher');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new', $this->_getStoreParams());
    }

    private function _getStoreParams()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        return is_null($storeId) ? array() : array('store' => $storeId);
    }

}

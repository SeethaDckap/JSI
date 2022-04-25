<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock;


class Filter extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }


    protected function _prepareCollection()
    {
        $collection = $this->getCollection();
        if($this->_getStoreParam()){
        $collection->addFieldToFilter('store_id', $this->_getStoreParam());
        }
        return parent::_prepareCollection();
    }

    protected function _getStoreParam()
    {
        $storeId =$this->getRequest()->getParam('store');
        return $storeId;
    }

}

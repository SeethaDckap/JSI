<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Mapping;

class DataMapping extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Grid {

    public function _construct() { 
        $this->_controller = 'adminhtml\Mapping_DataMapping';
        $this->_blockGroup = 'Epicor_Common';
        $this->_headerText = __('Data Mapping');
        parent::_construct();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new', $this->_getStoreParams());
    }

    private function _getStoreParams()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $storeParams = array('store'=>$storeId);
        return $storeParams;
    }
    
}

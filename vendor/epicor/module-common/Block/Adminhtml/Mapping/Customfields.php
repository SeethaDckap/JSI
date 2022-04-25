<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Mapping;

class Customfields extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Grid {

    public function _construct() { 
        $this->_controller = 'adminhtml\Mapping_Customfields';
        $this->_blockGroup = 'Epicor_Common';
        $this->_headerText = __('Custom Fields Mapping');
        parent::_construct();
        
       // $this->removeButton('add');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new', $this->_getStoreParams());
    }

    private function _getStoreParams()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $preselecteds = $this->_request->getParam('preselectedFields');
        $msec = $this->_request->getParam('msec');
        $params = array();
        if($preselecteds && is_null($storeId)) {
            $params = array('preselectedFields'=>$preselecteds,'msec'=>$msec);
        }
        $storeparams = array('store'=>$storeId);
        if($preselecteds && !is_null($storeId)) {
            $storeparams = array('preselectedFields'=>$preselecteds,'store' => $storeId,'msec'=>$msec);
        }

        return is_null($storeId) ? $params : $storeparams;
    }
    
}

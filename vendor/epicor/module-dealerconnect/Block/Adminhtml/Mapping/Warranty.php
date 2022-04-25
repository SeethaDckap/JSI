<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Adminhtml\Mapping;

class Warranty extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Grid {

    public function _construct() { 
        $this->_controller = 'adminhtml\Mapping_Warranty';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = __('Warranty Mapping');
        
        parent::_construct();
        
       // $this->removeButton('add');
    }   
    
}

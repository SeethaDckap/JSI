<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Adminhtml\Mapping;

class Pac extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Grid {

    public function _construct() { 
        $this->_controller = 'adminhtml\Mapping_Pac';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = __('Pac Mapping');
        
        parent::_construct();
        
        $this->removeButton('add');
    }   
    
}

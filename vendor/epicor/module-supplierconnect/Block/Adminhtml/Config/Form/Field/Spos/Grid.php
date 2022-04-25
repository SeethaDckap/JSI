<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Spos;


class Grid extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid
{

    protected $_messageBase = 'supplierconnect';
    protected $_messageType = 'spos';
    protected $_allowOptions = true;
    
    protected function _construct()
    {
        parent::_construct();
        $this->setHtmlId('_supplierconnect_grid_spos');
    }     

}

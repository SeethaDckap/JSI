<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Widget\Grid;

Class Serializer extends \Magento\Backend\Block\Widget\Grid\Serializer
{
    /**
     * @var string
     */
    //protected $_template = 'Epicor_Common::widget/grid/container.phtml';
    
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Common::widget/grid/serializer.phtml');
    }
}
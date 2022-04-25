<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Widget\Grid;

class Extended extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected function _construct()
    {
        parent::_construct();
        $this->setMassactionBlockName('Epicor\Common\Block\Widget\Grid\Massaction\Extended');
    }
}
<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml;


class Container extends \Magento\Backend\Block\Widget\Form
{

    private $_headerText;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('epicor_comm/container.phtml');
    }

    public function getHeaderText()
    {
        return $this->_headerText;
    }

    public function setHeaderText($text)
    {
        $this->_headerText = $text;
    }

}

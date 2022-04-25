<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Header
 *
 * @author David.Wylie
 */
class Header extends \Magento\Backend\Block\Widget\Form
{

    private $header = '';
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('epicor_comm/header.phtml');
    }

    public function setHeaderText($header)
    {
        $this->header = $header;
    }

    public function getHeaderText()
    {
        return $this->header;
    }

}

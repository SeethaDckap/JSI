<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Form\Element;


class Erpdefaultcontractaddress extends \Magento\Framework\Data\Form\Element\AbstractElement
{

    protected $_element;

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $selectHtml = '<div id="appendcontractaddress"></div>';

        return $selectHtml;
    }

}

<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\Lib\Varien\Data\Form\Element;


class Heading extends \Magento\Framework\Data\Form\Element\AbstractElement
{

    protected function _construct()
    {
        $this->setType('heading');
        parent::_construct();
    }

    public function getLabelHtml($idSuffix = '',$scopeLabel = '')
    {
        return '';
    }

    public function getElementHtml()
    {
        $tag = $this->getSubheading() ? 'h5' : 'h4';

        $html = '<div class="system-fieldset-sub-head" style="margin-left:-205px;">'
            . '<' . $tag . '>'
            . $this->getLabel()
            . '</' . $tag . '>'
            . '</div>';

        $html .= $this->getAfterElementHtml();
        return $html;
    }
}
<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * Catalog Custom Options config field disbale 
 */
namespace Epicor\Comm\Block\System\Config\Form\Field;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Disable  extends Field
{
    /**
     * @param AbstractElement $element
     * @return string     
     */
    protected function _getElementHtml(AbstractElement $element)
    {      
        if ($this->_scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != 'bistrack') {
            if ($element->getType() == 'select') {
                $element->setDisabled('disabled');
            } elseif ($element->getType() == 'text') {
                $element->setReadonly('true');
            }
        }

        return $element->getElementHtml();
    }
}

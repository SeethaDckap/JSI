<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\Lib\Varien\Data\Form\Element;


class Taxcodedefault extends \Magento\Framework\Data\Form\Element\AbstractElement
{

    protected function _construct()
    {
        $this->setType('select');
        parent::_construct();
    }

    public function getLabelHtml($idSuffix = '', $scopeLabel = '') {
        return '';
    }

    public function getElementHtml()
    {
        $html = '<div class="system-fieldset-sub-head" style="'.$this->getStyle().'"><h4>'.$this->getLabel().'</h4>'.
//        $html = '<div class="system-fieldset-sub-head" style="margin-left:-205px;"><h4>'.$this->getLabel().'</h4>'.
            '<input style="'.$this->getStyle().'" class = "'.$this->getClass().'" id="'.$this->getName().'" label="'.$this->getLabel().'" legend="'.$this->getLegend()
            .'" value = "'.$this->getValue().'"/></div>';
//        $html .= $this->getAfterElementHtml();
//        return $html;
        return array(
            array('value' => '', 'label' => ""),
            array('value' => '_attributes/default', 'label' => "unitOfMeasure -> default"),
            array('value' => 'description', 'label' => "unitOfMeasure -> description"),
            array('value' => 'code', 'label' => "unitOfMeasure -> code"),
            array('value' => 'currencies/currency', 'label' => "unitOfMeasure -> currencies -> currency (Repeating Group)"),
            array('value' => 'attributes/attribute', 'label' => "unitOfMeasure -> attributes -> attribute (Repeating Group)"),
        );
        //   return $html;

    }
}
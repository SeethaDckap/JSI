<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Widget\Form\Renderer;


class FromDate extends \Magento\Backend\Block\Widget\Form\Renderer\Element
{
    protected $_template = 'Epicor_Common::widget/form/renderer/element.phtml';
    
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
          return $this->getLayout()->createBlock('Epicor\SalesRep\Block\Widget\FromDateWidget')->toHtml();
    }
}

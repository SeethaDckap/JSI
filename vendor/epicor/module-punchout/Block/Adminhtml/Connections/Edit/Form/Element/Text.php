<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Punchout\Block\Adminhtml\Connections\Edit\Form\Element;

use Magento\Framework\Data\Form\Element\Text as TextElement;

/**
 * Class SecretKey
 */
class Text extends TextElement
{


    /**
     * Render HTML for element's label
     *
     * @param string $idSuffix   ID suffix.
     * @param string $scopeLabel Socpe label.
     *
     * @return string
     */
    public function getLabelHtml($idSuffix='', $scopeLabel='')
    {
        $scopeLabel = $scopeLabel ? ' data-config-scope="'.$scopeLabel.'"' : '';

        if ($this->getLabel() !== null) {
            $html = '<div class="admin__field-label" 
            for="'.$this->getHtmlId().$idSuffix.'"'.$this->_getUiId(
                'label'
            ).'><label><span'.$scopeLabel.'>'.$this->_escape(
            $this->getLabel()
            ).'</label></span></div>'."\n";
        } else {
            $html = '';
        }

        return $html;

    }//end getLabelHtml()


    /**
     * Get the default html.
     *
     * @return mixed
     */
    public function getDefaultHtml()
    {
        $html     = $this->getData('default_html');
        $required = $this->getRequired() ? ' _required' : '';
        if ($html === null) {
            $html = $this->getNoSpan() === true ? '' : '<div class="admin__field'.$required.'">'."\n";
            $html .= $this->getLabelHtml();
            $html .= $this->getElementHtml();
            $html .= $this->getNoSpan() === true ? '' : '</div>'."\n";
        }

        return $html;

    }//end getDefaultHtml()


    /**
     * Get the Html for the element.
     *
     * @return string
     */
    public function getElementHtml()
    {
        return '<div class="admin__field-control">'.parent::getElementHtml().'</div>';

    }//end getElementHtml()


}//end class


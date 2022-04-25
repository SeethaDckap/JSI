<?php

namespace Epicor\Dealerconnect\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Pacinformation extends \Magento\Config\Block\System\Config\Form\Field
{
//    const CONFIG_PATH = 'dealerconnect_enabled_messages/DEID_request/pacinfo';
 
    protected $_template = 'Epicor_Dealerconnect::epicor/dealerconnect/system/config/pacinfocheckbox.phtml';
 
    protected $_values = null;
    
    protected $_configPath;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    /**
     * Retrieve element HTML markup.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setNamePrefix($element->getName())
            ->setHtmlId($element->getHtmlId());
 
        return $this->_toHtml();
    }
     
    public function getValues()
    {
        $values = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
 
        foreach ($objectManager->create('Epicor\Dealerconnect\Model\Config\Source\Checkbox')->toOptionArray() as $value) {
            $values[$value['value']] = $value['label'];
        }
 
        return $values;
    }
    /**
     * 
     * @param  $name 
     * @return boolean
     */
    public function getIsChecked($name)
    {
        return in_array($name, $this->getCheckedValues());
    }
    /**
     * 
     *get the checked value from config
     */
    public function getCheckedValues()
    {
        
        $htmlId = explode('_', $this->getHtmlId());
        $messageName = $htmlId[3];
        $pacId = $htmlId[5];
        $this->_configPath = 'dealerconnect_enabled_messages/'.$messageName.'_request/'.$pacId ;
        $this->_values = null;
        if (is_null($this->_values)) {
            $data = $this->getConfigData();
            if (isset($data[$this->_configPath])) {
                $data = $data[$this->_configPath];
            } else {
                $data = '';
            }
            $this->_values = explode(',', $data);
        }
 
        return $this->_values;
    }
    
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td class="" colspan="5">';
        $html .='<table cellpadding="2" cellspacing="4" style="border-bottom:0px !important;border: 1px solid burlywood;margin-top:24px;border-collapse: separate;border-radius: 5px;background: oldlace;"><tr><td>';
        $html .='<h1 style="padding:0px 0px 0px 10px">Information Section</h1>';
        $html .='<div style="margin-left:10px;">';
        $html .='<h4 style="padding: 0px;">'. $element->getLabel() .'</h4>';
        $html .= '<br>';
        $html .= $this->_getElementHtml($element);
        $html .='</div>';
        $html .= '</td></tr></table><style>#addToManageBtn_deid { display: none;}</style>';
        return $html;
    }

}
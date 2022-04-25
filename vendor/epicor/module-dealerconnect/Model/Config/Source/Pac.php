<?php

namespace Epicor\Dealerconnect\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Pac extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'Epicor_Dealerconnect::epicor/dealerconnect/system/config/paccheckbox.phtml';
 
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
    public function getIsChecked($name, $htmlId)
    {
        $htmlId = explode('_', $htmlId);
        $messageName = $htmlId[3];
        $pacId = $htmlId[5];
        $this->_configPath = 'dealerconnect_enabled_messages/'.$messageName.'_request/'.$pacId ;
        $this->_values = null;
        
        return in_array($name, $this->getCheckedValues());
    }
    /**
     * 
     *get the checked value from config
     */
    public function getCheckedValues()
    {
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

}
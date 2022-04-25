<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Renderer;


class Select extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;
    
    
    protected $arpaymentsHelper;
    
    /**
     * @var array
     */
    protected $_values;

    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter
     */
    protected $_converter;    

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter,
        array $data = []
    ) {
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->_converter = $converter;
        $this->setType('checkbox');
        $this->setExtType('checkboxes');        
        parent::__construct(
            $context,
            $jsonEncoder,
            $data
        );
    }
    
    public function getHtmlAttributes()
    {
        return [
            'type',
            'name',
            'class',
            'style',
            'checked',
            'onclick',
            'onchange',
            'disabled',
            'data-role',
            'data-action'
        ];
    }    


    public function render(\Magento\Framework\DataObject $row)
    {
        $values = $this->_getValues();
        if($row->getSelectArpayments() =="Totals") {
            $html = '';
            $disableClass ='';
            $id = $row->getId(); 
            return $html;
        } else {
            $value = $row->getData($this->getColumn()->getIndex());
            $checked = '';
            if (is_array($values)) {
                $checked = in_array($value, $values) ? ' checked="checked"' : '';
            } else {
                $checkedValue = $this->getColumn()->getValue();
                if ($checkedValue !== null) {
                    $checked = $value === $checkedValue ? ' checked="checked"' : '';
                }
            }
            $id = 'id_' . $row->getData('invoice_number');
            $html = '<input '.$checked.' type="checkbox" name="' . $this->getColumn()->getFieldName() . '"  id="' . $id . '"';
            $html .= ' class="checkbox admin__control-checkbox"';
            $html .= ' value="' .
                $row->getData('invoice_number').
                '" />' ;
            return $html;            
        }
    }
    
    /**
     * Returns values of the column
     *
     * @return array
     */
    public function getValues()
    {
        if ($this->_values === null) {
            $this->_values = $this->getColumn()->getData('values') ? $this->getColumn()->getData('values') : [];
        }
        return $this->_values;
    }

    /**
     * Prepare data for renderer
     *
     * @return array
     */
    protected function _getValues()
    {
        $values = $this->getColumn()->getValues();
        return $this->_converter->toFlatArray($values);
    }    

}

?>
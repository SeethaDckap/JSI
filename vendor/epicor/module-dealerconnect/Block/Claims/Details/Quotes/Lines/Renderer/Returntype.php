<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes\Lines\Renderer;


/**
 * RFQ Line Return Type renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Returntype extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * 
     * @var array
     */
    protected $options = array();
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Helper\Data $dealerconnectHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->options = $dealerconnectHelper->getEccReturnTypeOptions();
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        $index = $this->getColumn()->getIndex();
        $returnType = $row->getData($index);
        $html = '';

        if($key == 'new'){
            $html = '<select class="lines_line_ecc_return_type_select" name="">';
                        foreach ($this->options as $key => $returnType) {
                            $selected = ($returnType == 'Replace') ? 'selected="selected"' : '';
                            $html .= '<option value="' . $key . '" '. $selected .'>' . $returnType . '</option>';
                        };

        } else if ($this->registry->registry('rfqs_editable')) {
            if ($returnType != '' && isset($this->options[$returnType])) {
                $html = '<span class="ecc_return_type_field">
                            <input type="hidden" class="lines_line_ecc_return_type_field" value="' . $returnType . '" name="lines[' . $key . '][' . $row->getUniqueId() . '][ecc_return_type]" />
                            <span class="lines_line_ecc_return_type_display">' . $this->options[$returnType] .'</span>
                        </span>';
            } else {
                $html = '<span></span>';
            }
        } else if (isset($this->options[$returnType])) {
            $html = $this->options[$returnType] . '<input name="lines[' . $key . '][' . $row->getUniqueId() . '][ecc_return_type]" type="hidden" value="' . $this->options[$returnType] . '" />';
        }

        return $html;
    }

}

<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Config\Form\Field;


class EwaCartDisplayOptions extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected $_ewaConfiguratorDisplay;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layoutInterface;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->layoutInterface = $context->getLayout();
        $this->addColumn('ewacartsortorder', array(
            'label' => __('Kinetic/EWA Configurator Cart Sort Order'),
            'style' => 'width:150px',
            'renderer' => $this->_getEwaConfiguratorRenderer(),
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _getEwaConfiguratorRenderer()
    {
        if (!$this->_ewaConfiguratorDisplay) {
            $this->_ewaConfiguratorDisplay = $this->layoutInterface->createBlock(
                'Epicor\Comm\Block\Adminhtml\Form\Field\EwaCartDisplayOptions', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_ewaConfiguratorDisplay->setInputName('ewacartsortorder')
                ->setClass('rel-to-selected');
        }
        return $this->_ewaConfiguratorDisplay;
    }
    
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $getDatas = $row->getData('ewacartsortorder');
        $optionExtraAttr['option_' . $this->_getEwaConfiguratorRenderer()->calcOptionHash($row->getData('ewacartsortorder'))] = 'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );  
    }      


}
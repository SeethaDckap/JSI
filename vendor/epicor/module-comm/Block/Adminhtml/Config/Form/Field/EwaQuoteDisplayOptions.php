<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Config\Form\Field;


class EwaQuoteDisplayOptions extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected $_ewaQuoteConfiguratorDisplay;

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
        $this->addColumn('ewaquotesortorder', array(
            'label' => __('Kinetic/EWA Configurator Quote Sort Order'),
            'style' => 'width:150px',
            'renderer' => $this->_getEwaQuoteConfiguratorRenderer(),
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _getEwaQuoteConfiguratorRenderer()
    {
        if (!$this->_ewaQuoteConfiguratorDisplay) {
            $this->_ewaQuoteConfiguratorDisplay = $this->layoutInterface->createBlock(
                'Epicor\Comm\Block\Adminhtml\Form\Field\EwaQuoteDisplayOptions', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_ewaQuoteConfiguratorDisplay->setInputName('ewaquotesortorder')
                ->setClass('rel-to-selected');
        }
        return $this->_ewaQuoteConfiguratorDisplay;
    }
    
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $getDatas = $row->getData('ewaquotesortorder');
        $optionExtraAttr['option_' . $this->_getEwaQuoteConfiguratorRenderer()->calcOptionHash($row->getData('ewaquotesortorder'))] = 'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );  
    }      


}
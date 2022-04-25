<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Config\Form\Field;


class Payment extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected $_paymentMethodRenderer;
    protected $_chargeTypeRenderer;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layoutInterface;

    protected function _getPaymentMethodRenderer()
    {
        if (!$this->_paymentMethodRenderer) {
            $this->_paymentMethodRenderer = $this->layoutInterface->createBlock(
                'Epicor\Comm\Block\Adminhtml\Form\Field\Payment', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_paymentMethodRenderer->setInputName('paymentmethod')
                ->setClass('rel-to-selected');
        }
        return $this->_paymentMethodRenderer;
    }

    protected function _getChargeTypeRenderer()
    {
        if (!$this->_chargeTypeRenderer) {
            $this->_chargeTypeRenderer = $this->layoutInterface->createBlock(
                'Epicor\Comm\Block\Adminhtml\Form\Field\Chargetype', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_chargeTypeRenderer->setInputName('chargetype')
                ->setClass('rel-to-selected');
        }
        return $this->_chargeTypeRenderer;
    }
    
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $getDatas = $row->getData('ewaquotesortorder');
        $optionExtraAttr['option_' . $this->_getChargeTypeRenderer()->calcOptionHash($row->getData('chargetype'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getPaymentMethodRenderer()->calcOptionHash($row->getData('paymentmethod'))] = 'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );  
    }      

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->layoutInterface = $context->getLayout();
        $this->addColumn('paymentmethod', array(
            'label' => __('Payment Method'),
            'style' => 'width:150px',
            'renderer' => $this->_getPaymentMethodRenderer(),
        ));
        $this->addColumn('chargetype', array(
            'label' => __('Type'),
            'style' => 'width:120px',
            'renderer' => $this->_getChargeTypeRenderer(),
        ));
        $this->addColumn('amount', array(
            'label' => __('Value'),
            'style' => 'width:75px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::__construct(
            $context,
            $data
        );
    }

}

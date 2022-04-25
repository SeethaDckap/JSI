<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Form\Field;


class Payment extends \Magento\Framework\View\Element\Html\Select
{

    private $_paymentMethods;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Paymentmethods
     */
    protected $paymentMethods;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Epicor\Comm\Model\Erp\Mapping\Paymentmethods $paymentMethods,
        array $data = []
    ) {
        $this->paymentMethods=$paymentMethods;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _getPaymentMethods()
    {
        if (is_null($this->_paymentMethods)) {
            $this->_paymentMethods = $this->paymentMethods->getPaymentMethodList(true, true, true);
        }
        return $this->_paymentMethods;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setColumnName($value)
    {
        //M1 > M2 Translation Begin (Rule 22)
        //return $this->setExtraParams('rel="#{paymentmethod}" style="width:150px');
        return $this->setExtraParams('rel="<%- paymentmethod %>" style="width:50px"');
        //M1 > M2 Translation End
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('ALL', __('ALL Payment Methods'));
            foreach ($this->_getPaymentMethods() as $pm) {
                $this->addOption($pm['value'], $pm['label']);
            }
        }
        return parent::_toHtml();
    }

}

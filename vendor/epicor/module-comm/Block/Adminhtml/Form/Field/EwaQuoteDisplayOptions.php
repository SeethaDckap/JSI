<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Form\Field;


class EwaQuoteDisplayOptions extends \Magento\Framework\View\Element\Html\Select
{

    private $_ewaConfigurator;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    ) {
        $this->commHelper = $commHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _getConfiguratorOptions()
    {
        if (is_null($this->_ewaConfigurator)) {
            $this->_ewaConfigurator = $this->commHelper->ewaConfiguratorValues(true, true, true);
        }
        return $this->_ewaConfigurator;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setColumnName($value)
    {
        //M1 > M2 Translation Begin (Rule 22)
        //return $this->setExtraParams('rel="#{paymentmethod}" style="width:150px');
        return $this->setExtraParams('rel="<%- ewaquotesortorder %>" style="width:200px"');
        //M1 > M2 Translation End
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getConfiguratorOptions() as $co) {
                $this->addOption($co['value'], $co['label']);
            }
        }
        return parent::_toHtml();
    }

}

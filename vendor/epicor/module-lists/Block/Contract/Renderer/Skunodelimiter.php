<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Contract\Renderer;


class Skunodelimiter extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Lists\Helper\Messaging\Customer
     */
    protected $listsMessagingCustomerHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper,
        array $data = []
    ) {
        $this->listsMessagingCustomerHelper = $listsMessagingCustomerHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $fullsku = $row->getSku();
        $delimiter = $this->listsMessagingCustomerHelper->getUOMSeparator();
        $sku = explode($delimiter, $fullsku);
        return $sku[0];
    }

}

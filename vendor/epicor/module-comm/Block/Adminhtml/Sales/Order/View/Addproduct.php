<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Sales\Order\View;


class Addproduct extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->backendHelper = $backendHelper;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
    }


    public function getAddProductUrl()
    {
        return $this->backendHelper->getUrl("adminhtml/epicorcomm_sales_order/addproduct/", array('order_id' => $this->getOrderId()));
    }

    public function getSaveProductUrl()
    {
        return $this->backendHelper->getUrl("adminhtml/epicorcomm_sales_order/saveproducts/", array('order_id' => $this->getOrderId()));
    }

    public function getAddProductBtnVisability()
    {
        return $this->scopeConfig->isSetFlag('Epicor_Comm/payments/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? 'true' : 'false';
    }

    protected function getOrderId()
    {
        return $this->getRequest()->get('order_id');
    }

}

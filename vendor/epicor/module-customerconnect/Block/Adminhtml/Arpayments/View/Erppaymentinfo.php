<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Arpayments\View;

class Erppaymentinfo extends \Magento\Backend\Block\Template
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'arpayments/view/tab/erppaymentinfo.phtml';
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->adminHelper = $adminHelper;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_ar_order');
    }
    
    public function getErpStatusUrl()
    {
        return $this->getUrl('adminhtml/arpayments/erpstatus', ['arpayment_id' => $this->getOrder()->getEntityId()]);
    }
    
    public function getManuallySet()
    {
        return strpos($this->getOrder()->getEccCaapMessage(), 'Manually set to :') !== false;
    }
    
    public function getErpOrderNumber()
    {
        return $this->getOrder()->getErpArpaymentsOrderNumber() ? $this->getOrder()->getErpArpaymentsOrderNumber() : "-";
    }

    /**
     * Replace links in string
     *
     * @param array|string $data
     * @param null|array $allowedTags
     * @return string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->adminHelper->escapeHtmlWithLinks($data, $allowedTags);
    }
    
    /**
     * Returns ERP Payment Status
     * 
     * @return array
     */
    public function getStatuses()
    {
        return array(
            '0' => 'Payment Not Sent',
            '1' => 'Payment Sent',
            '3' => 'Erp Error'
        );
    }
}

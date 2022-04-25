<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Adminhtml\Arpayments;

use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;

/**
 * Class CommentsHistory
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Erppaymentinfo extends \Epicor\Customerconnect\Controller\Adminhtml\Arpayments
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param \Epicor\Comm\Controller\Adminhtml\Context $context
     * @param \Epicor\Customerconnect\Api\OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Epicor\Comm\Model\CustomerFactory $customer
     * @param \Magento\Framework\Session\GenericFactory $generic
     * @param \Epicor\Customerconnect\Model\ArPayment\Order\PaymentFactory $salesOrderPayment
     * @param \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPayment
     * @param \Epicor\Customerconnect\Model\Message\Request\CaapFactory $commMessageRequestCaapFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Customerconnect\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        LoggerInterface $logger,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Framework\Session\GenericFactory $generic,
        \Epicor\Customerconnect\Model\ArPayment\Order\PaymentFactory $salesOrderPayment,
        \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPayment,
        \Epicor\Customerconnect\Model\Message\Request\CaapFactory $commMessageRequestCaapFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->layoutFactory = $layoutFactory;
        parent::__construct(
            $context,
            $orderRepository,
            $backendAuthSession,
            $logger,
            $translateInline,
            $resultRawFactory,
            $customer,
            $generic,
            $salesOrderPayment,
            $commErpMappingPayment,
            $commMessageRequestCaapFactory
        );
    }

    /**
     * Generate order history for ajax request
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $this->_initArpayment();
        $layout = $this->layoutFactory->create();
        $html = $layout->createBlock(\Epicor\Customerconnect\Block\Adminhtml\Arpayments\View\Erppaymentinfo::class)
            ->toHtml();
        $this->_translateInline->processResponseBody($html);
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($html);
        return $resultRaw;
    }
}

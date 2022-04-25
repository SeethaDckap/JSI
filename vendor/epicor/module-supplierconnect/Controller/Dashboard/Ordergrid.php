<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Dashboard;

class Ordergrid extends \Epicor\Supplierconnect\Controller\Dashboard
{

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuad
     */
    protected $customerconnectMessageRequestCuad;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    protected $layoutFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Model\Message\Request\Cuad $customerconnectMessageRequestCuad,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->customerconnectMessageRequestCuad = $customerconnectMessageRequestCuad;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->layoutFactory = $layoutFactory;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    /**
     * Index action 
     */
    public function execute()
    {
        $output = $this->getLayoutFactory()->create()->createBlock('Epicor\Supplierconnect\Block\Customer\Dashboard\Ordergrids')->toHtml();
        $this->getResponse()->appendBody($output);
    }

    /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactory()
    {
        return $this->layoutFactory;
    }

}

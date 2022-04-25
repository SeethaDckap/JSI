<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller;


/**
 * Display controller
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
abstract class Rfq extends \Epicor\Supplierconnect\Controller\Generic
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->generic = $generic;
        $this->request = $request;
        $this->commHelper = $commHelper;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
}

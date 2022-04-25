<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Helper;


class Dashboard extends \Epicor\Comm\Helper\Messaging
{

    /**
     * @var \Epicor\Common\Helper\Locale\Format\Date
     */


    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;


    protected $commonLocaleFormatDateHelper;

    protected $translationStateInterface;

    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\customerSession
     */
    protected $customerSession;

    protected $_localeDate;

    protected $supplierconnectMessageRequestSusd;

    protected $commHelper;

    protected $_customerInfos;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    protected $logger;

    protected $manageDashboardFactory;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Epicor\Common\Helper\Locale\Format\Date $commonLocaleFormatDateHelper,
        \Epicor\Supplierconnect\Logger\Logger $logger,
        \Epicor\Supplierconnect\Model\Message\Request\Susd $supplierconnectMessageRequestSusd,
        \Epicor\Supplierconnect\Model\ResourceModel\ManageDashboard\CollectionFactory $manageDashboardFactory
    )
    {
        $this->commonLocaleFormatDateHelper = $commonLocaleFormatDateHelper;
        $this->transportBuilder = $context->getEmailTemplateFactory();
        $this->translationStateInterface = $context->getTranslateInterface();
        $this->storeManager = $context->getStoreManager();
        $this->customerSession = $context->getCustomerSession();
        $this->_localeDate = $context->getTimezone();
        $this->supplierconnectMessageRequestSusd = $supplierconnectMessageRequestSusd;
        $this->commHelper = $context->getCommHelper();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->logger = $logger;
        $this->manageDashboardFactory = $manageDashboardFactory;
        parent::__construct($context);
    }

    /**
     * Converts a date / timestamp to the format specified, using magento locale dates
     *
     * @param string $timestamp
     * @param string $format
     *
     * @return string
     */
    public function getLocalDate($timestamp, $format = \IntlDateFormatter::MEDIUM, $showTime = false)
    {

        $helper = $this->commonLocaleFormatDateHelper;
        return $helper->getLocalDate($timestamp, $format, $showTime);
    }



    public function getDashboardInformation() {
        $customer = $this->customerSession->getCustomer();
        $dashboardFactory = $this->manageDashboardFactory->create();
        $dashboardFactory->addFieldToFilter('customer_id',$customer->getId());
        $items = $dashboardFactory->getFirstItem()->getData();
        return $items;
    }



}
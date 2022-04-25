<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Dashboard;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method int getColumnCount()
 * @method void setColumnCount(int $count)
 * @method bool getOnRight()
 * @method void setOnRight(bool $bool)
 * @method bool getOnLeft()
 * @method void setOnLeft(bool $bool)
 */
class Reminder extends \Magento\Framework\View\Element\Template
{

    /**
     *  @var \Magento\Framework\DataObject
     */
    protected $_infoData = array();
    protected $_extraData = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    protected $urlEncoder;

    protected $scopeConfig;

    protected $SupplierReminderFactory;

    protected $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Epicor\Supplierconnect\Model\SupplierReminderFactory $SupplierReminderFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->registry = $registry;
        $this->backendHelper = $backendHelper;
        $this->_localeResolver = $localeResolver;
        $this->urlEncoder = $urlEncoder;
        $this->scopeConfig = $context->getScopeConfig();
        $this->SupplierReminderFactory = $SupplierReminderFactory;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     *
     * @return \Epicor\Supplierconnect\Helper\Data
     */
    public function getHelper()
    {
        return $this->supplierconnectHelper;
    }

    public function getRfqsData() {
        $customer = $this->customerSession->getCustomer();
        $rfqsRemainderFactor = $this->SupplierReminderFactory->create();
        $rfqsRemainderFactor->load($customer->getId(),'customer_id');
        return $rfqsRemainderFactor;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getInfoData()
    {
        return $this->_infoData;
    }

    public function getExtraData()
    {
        return $this->_extraData;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

}

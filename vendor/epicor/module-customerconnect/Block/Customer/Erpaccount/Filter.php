<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Erpaccount;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method void setOnRight(bool $bool)
 * @method bool getOnRight()
 */
class Filter extends \Magento\Directory\Block\Data
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('customerconnect/erpaccount/filter.phtml');

        $erpAcctId = $this->customerSession->getCustomer()->getEccErpaccountId();
        $erpAcct = $this->commCustomerErpaccountFactory->create()->load($erpAcctId);
        /* @var $erpAcct Epicor_Comm_Model_Customer_Erpaccount */
        $this->setErpAccountId($erpAcct);
        $childArray = array();
        foreach ($erpAcct->getChildAccounts() as $child) {
            if (!in_array($child->getAccountNumber(), $childArray)) {
                $childArray[] = $child->getAccountNumber();
            }
        }

        $this->setChildAccounts($childArray);
    }

}

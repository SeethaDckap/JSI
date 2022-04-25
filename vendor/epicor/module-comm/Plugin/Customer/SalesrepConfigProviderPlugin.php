<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Comm
 * @subpackage Plugin
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Customer;

use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Address\CustomerAddressDataFormatter;
use Magento\Customer\Model\Address\CustomerAddressDataProvider;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Http\Context as HttpContext;
use Epicor\Comm\Helper\Data;

/**
 * Plugin for checkout default Config Provider
 */
class SalesrepConfigProviderPlugin
{

    /**
     * Customer session model
     *
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Context data for requests
     *
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * Factory class for @see \Magento\Customer\Api\Data\CustomerInterface
     *
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * Comm Helper
     *
     * @var Data
     */
    protected $commHelper;

    /**
     * Data object helper
     *
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Provides customer address data.
     *
     * @var CustomerAddressDataProvider
     */
    protected $customerAddressData;

    /**
     * @var CustomerAddressDataFormatter
     */
    private $customerAddressDataFormatter;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;


    /**
     * Constructor function
     *
     * @param CustomerSession             $customerSession     Customer session model.
     * @param HttpContext                 $httpContext         Context data for requests.
     * @param DataObjectHelper            $dataObjectHelper    Data object helper.
     * @param CustomerInterfaceFactory    $customerDataFactory Factory class for CustomerInterface.
     * @param CustomerAddressDataProvider $customerAddressData Provides customer address data.
     * @param Data                        $commHelper          Comm Helper.
     */
    public function __construct(
        CustomerSession $customerSession,
        HttpContext $httpContext,
        DataObjectHelper $dataObjectHelper,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerAddressDataProvider $customerAddressData,
        Data $commHelper,
        CustomerAddressDataFormatter $customerAddressDataFormatter,
        CustomerRepository $customerRepository
    ) {
        $this->customerSession     = $customerSession;
        $this->httpContext         = $httpContext;
        $this->dataObjectHelper    = $dataObjectHelper;
        $this->customerDataFactory = $customerDataFactory;
        $this->commHelper          = $commHelper;
        $this->customerAddressData = $customerAddressData;
        $this->customerAddressDataFormatter = $customerAddressDataFormatter;
        $this->customerRepository = $customerRepository;

    }//end __construct()


    /**
     * Return configuration array
     *
     * @param DefaultConfigProvider $subject Default Config Provider.
     * @param array                 $result  Return configuration array.
     *
     * @return array|mixed
     * @throws LocalizedException Localized exception.
     */
    public function afterGetConfig(DefaultConfigProvider $subject, array $result)
    {
        if ($this->isSalesRepAndMasquerading()) {
            unset($result['customerData']);
            $result['customerData'] = $this->getCustomerData();
        }else{
            //this will use when we load limited addresses in checkout page as like WSO-8546
           //unset($result['customerData']);
            //$result['customerData'] = $this->getCustomerData();
        }
        return $result;

    }//end afterGetConfig()


    /**
     * Is logged in customer of type Salesrep and is masquerading.
     *
     * @return boolean
     */
    protected function isSalesRepAndMasquerading()
    {
        $customer = $this->customerSession->getCustomer();

        /*
         * Customer Model
         *
         * @var \Epicor\Comm\Model\Customer  $customer
         */

        $helper = $this->commHelper;

        /*
         * Comm Helper
         *
         * @var \Epicor\Comm\Helper\Data $helper
         */

        if ($this->isCustomerLoggedIn() && $customer->isSalesRep() && $helper->isMasquerading()) {
            return true;
        }

        return false;

    }//end isSalesRepAndMasquerading()


    /**
     * Retrieve customer data
     *
     * @return array
     *
     * @throws LocalizedException Localized Exception.
     */
    protected function getCustomerData()
    {
        $customerData = [];
        if ($this->isCustomerLoggedIn()) {
            $customer          = $this->customerSession->getCustomer();
            $salesRepAddresses = $customer->getCustomAddresses(null, true);
            $salesRepAddresses = $salesRepAddresses['model'];

            /*
             * Factory class for @see \Magento\Customer\Api\Data\CustomerInterface
             *
             * @var CustomerInterface $customer
             */

            $customerDataObject = $this->customerDataFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $customerDataObject,
                $customerData,
                CustomerInterface::class
            );
            $customerDataObject->setAddresses($salesRepAddresses)->setId($this->customerSession->getCustomerId());

            $customerData              = $customerDataObject->__toArray();
            $customerData['addresses'] = $this->customerAddressData->getAddressDataByCustomer($customerDataObject);
        }//end if

        return $customerData;

    }//end getCustomerData()


    /**
     * Check if customer is logged in.
     *
     * @return boolean
     */
    protected function isCustomerLoggedIn()
    {
        return (bool) $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);

    }//end isCustomerLoggedIn()


}//end class

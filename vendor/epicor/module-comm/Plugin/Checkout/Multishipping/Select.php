<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Checkout\Multishipping;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\AddressRepositoryInterface;


/**
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Select
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\Filter\DataObject\GridFactory
     */
    protected $_filterGridFactory;

    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected $_multishipping;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    private $_addressConfig;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param AddressConfig $addressConfig
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Helper\Data $commonHelper,
        AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->commonHelper = $commonHelper;
        $this->addressRepository = $addressRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }
    public function restrictAddressTypes()
    {
        return $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve options for addresses dropdown
     *
     * @return array
     */
    public function afterGetAddress(
        \Magento\Multishipping\Block\Checkout\Address\Select $subject,
        $addresses
    )
    {
        $options=[];
        $customer = $subject->getCheckout()->getCustomerSession()->getCustomer();
        $addressesInvoice = ($this->restrictAddressTypes()) ? $customer->getAddressesByType('invoice') : $customer->getCustomAddresses();
        foreach ($addressesInvoice as $address) {
            $options[] = $address->getId();
        }
         try {
                $filter =  $this->filterBuilder->setField('entity_id')
                    ->setValue($options)
                    ->setConditionType('IN')
                    ->create();
                $addresses = (array)($this->addressRepository->getList(
                    $this->searchCriteriaBuilder->addFilters([$filter])->create()
                )->getItems());
            } catch (NoSuchEntityException $e) {
                return [];
            }
            $subject->setData('address_collection', $addresses);
        return $addresses;
    }

    public function canAddNew()
    {
        $helper = $this->commonHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        return $helper->customerAddressPermissionCheck('create');
    }

    //M1 > M2 Translation Begin (Rule p2-5.1)
    public function getFormKey()
    {
        return $this->formKey;
    }
    //M1 > M2 Translation End

}

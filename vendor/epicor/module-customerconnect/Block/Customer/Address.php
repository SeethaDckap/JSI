<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method void setOnRight(bool $bool)
 * @method bool getOnRight()
 */
class Address extends \Magento\Directory\Block\Data
{

    const FRONTEND_RESOURCE = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_BILLING_READ = 'Epicor_Customerconnect::customerconnect_account_information_billing_read';

    const FRONTEND_RESOURCE_BILLING_UPDATE = 'Epicor_Customerconnect::customerconnect_account_information_billing_update';
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_addressData;
    protected $_countryCode;
    protected $_showAddressCode = true;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * @var \Epicor\AccessRight\Helper\AccessRoles
     */
    protected $eccAccessRoles;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commonHelper = $commonHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        $this->eccAccessRoles = $this->_accessauthorization->getEccAccessRoles();
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
        $this->_addressData = array();
        $this->setTemplate('Epicor_Customerconnect::customerconnect/address.phtml');
        $this->setOnRight(false);
        $this->setShowName(true);
    }


    /**
     * @return bool
     */
    public function isAccessAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }

    public function toHtml()
    {
        if (!$this->isAccessAllowed(static::FRONTEND_RESOURCE_BILLING_READ)) {
            return '';
        }
        return parent::toHtml();
    }

    public function editAllowed()
    {
        if (!$this->isAccessAllowed(static::FRONTEND_RESOURCE_BILLING_UPDATE)) {
            return false;
        }
        return true;
    }

    public function getJsonEncodedData()
    {
        if (empty($this->_addressData)) {
            return json_encode(array());
        }
        $detailsArray = array(
            'name' => $this->_addressData->getName(),
            //M1 > M2 Translation Begin (Rule 9)
            /*'address1' => $this->_addressData->getAddress1(),
            'address2' => $this->_addressData->getAddress2(),
            'address3' => $this->_addressData->getAddress3(),*/
            'address1' => $this->_addressData->getData('address1'),
            'address2' => $this->_addressData->getData('address2'),
            'address3' => $this->_addressData->getData('address3'),
            //M1 > M2 Translation End
            'city' => $this->_addressData->getCity(),
            'county' => $this->_addressData->getCounty(),
            'country' => $this->_addressData->getCountry(),
            'postcode' => $this->_addressData->getPostcode(),
            'email' => $this->_addressData->getEmailAddress(),
            'telephone' => $this->_addressData->getTelephoneNumber(),
            'fax' => $this->_addressData->getFaxNumber(),
            'address_code' => $this->_addressData->getAddressCode()
        );
        return json_encode($detailsArray);
    }

    public function getAddressCode()
    {
        return $this->_addressData ? $this->_addressData->getAddressCode() : null;
    }

    public function getName()
    {
        if ($this->_addressData) {
            return $this->_addressData->getName();
        }
    }

    public function getCompany()
    {
        return $this->_addressData ? $this->_addressData->getCompany() : null;
    }

    public function getAddress1()
    {
        //M1 > M2 Translation Begin (Rule 9)
        //return $this->_addressData->getAddress1();
        if ($this->_addressData) {
            return $this->_addressData ? $this->_addressData->getData('address1') : null;
        }
        //M1 > M2 Translation End
    }

    public function getAddress2()
    {
        //M1 > M2 Translation Begin (Rule 9)
        //return $this->_addressData->getAddress2();
        if ($this->_addressData) {
            return $this->_addressData ? $this->_addressData->getData('address2') : null;
        }
        //M1 > M2 Translation End
    }

    public function getAddress3()
    {
        //M1 > M2 Translation Begin (Rule 9)
        return $this->_addressData ? $this->_addressData->getData('address3') : null;
        //M1 > M2 Translation End
    }

    public function getStreet()
    {
        //M1 > M2 Translation Begin (Rule 9)
        /*$street = $this->_addressData->getAddress1();
        $street .= $this->_addressData->getAddress2() ? ', ' . $this->_addressData->getAddress2() : '';
        $street .= $this->_addressData->getAddress3() ? ', ' . $this->_addressData->getAddress3() : '';*/
        $street = $this->_addressData->getData('address1');
        $street .= $this->_addressData->getData('address2') ? ', ' . $this->_addressData->getData('address2') : '';
        $street .= $this->_addressData->getData('address3') ? ', ' . $this->_addressData->getData('address3') : '';
        //M1 > M2 Translation End
        return $street;
    }

    public function getCity()
    {
        if ($this->_addressData) {
            return $this->_addressData ? $this->_addressData->getCity() : null;
        }
    }

    public function getCounty()
    {
        $helper = $this->customerconnectHelper;
        if ($this->_addressData) {
            $region = $helper->getRegionFromCountyName($this->getCountryCode(), $this->_addressData->getCounty());
            return ($region) ? $region->getName() : $this->_addressData->getCounty();
        } else
            return null;
    }

    public function getRegionId()
    {
        $helper = $this->customerconnectHelper;
        $region = $this->_addressData ? $helper->getRegionFromCountyName($this->getCountryCode(), $this->_addressData->getCounty()) : null;

        $regionId = ($region) ? $region->getId() : 0;
        return $regionId;
    }

    public function getPostcode()
    {
        return $this->_addressData ? $this->_addressData->getPostcode() : null;
    }

    public function getCountryCode()
    {

        if (is_null($this->_countryCode)) {
            $helper = $this->customerconnectHelper;
            if ($this->_addressData) {
                $this->_countryCode = $helper->getCountryCodeForDisplay($this->_addressData->getCountry(), $helper::ERP_TO_MAGENTO);
            }
        }

        return $this->_countryCode;
    }

    public function getCountry()
    {
        try {
            $helper = $this->customerconnectHelper;

            return $helper->getCountryName($this->getCountryCode());
        } catch (\Exception $e) {
            if ($this->_addressData) {
                return $this->_addressData ? $this->_addressData->getCountry() : null;
            }
        }
    }

    public function getTelephoneNumber()
    {
        return $this->_addressData ? $this->_addressData->getTelephoneNumber() : null;
    }

    public function getFaxNumber()
    {
        return $this->_addressData ? $this->_addressData->getFaxNumber() : null;
    }

    public function getEmail()
    {
        return $this->_addressData ? $this->_addressData->getEmailAddress() : null;
    }

    public function getCarriageText()
    {
        return $this->_addressData ? $this->_addressData->getCarriageText() ?: $this->_addressData->getEccInstructions() : null;
    }

    public function getAddressesHtmlSelect()
    {

        $options = array();

        $helper = $this->commMessagingHelper;
        $customer = $this->customerSession->getCustomer();

        if (!$customer->getId()) {
            $customer = $customer->load($this->customerSession->getId());
            $this->customerSession->setCustomer($customer);
        }

        $restrict = $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


        $type = $this->getAddressType();

        if ($type == 'quote') {
            $type = 'invoice';
        }

        $addressId = null;
        $addresses = ($restrict) ? $customer->getAddressesByType($type) : $customer->getCustomAddresses();
        foreach ($addresses as $address) {
            /* @var $address \Magento\Customer\Model\Address */
            $formatted = trim(ltrim(trim(str_replace($customer->getName() . ',', $address->getCompany() . ',', $address->format('oneline'))), ','));
            $options[] = array(
                'value' => $address->getId(),
                'label' => $formatted,
                'params' => array(
                    'data-iscustom' => $address->getIsCustom(),
                    'data-address' => htmlentities(json_encode(array(
                        'addressCode' => $address->getEccErpAddressCode(),
                        'name' => $helper->stripNonPrintableChars($address->getCompany()),
                        'address1' => $helper->stripNonPrintableChars($address->getStreet1()),
                        'address2' => $helper->stripNonPrintableChars($address->getStreet2()),
                        'address3' => $helper->stripNonPrintableChars($address->getStreet3()),
                        'city' => $helper->stripNonPrintableChars($address->getCity()),
                        'county' => $helper->stripNonPrintableChars($helper->getRegionNameOrCode($address->getCountry_id(), ($address->getRegionId() ? $address->getRegionId() : $address->getRegion()))),
//                        'county' => $helper->stripNonPrintableChars($address->getRegion()),
                        'country' => $helper->getErpCountryCode($address->getCountry_id()),
                        'postcode' => $helper->stripNonPrintableChars($address->getPostcode()),
                        'telephoneNumber' => $helper->stripNonPrintableChars($address->getTelephone()),
                        'mobileNumber' => $helper->stripNonPrintableChars($address->getEccMobileNumber()),
                        'faxNumber' => $helper->stripNonPrintableChars($address->getFax()),
                    )))
                )
            );
            if ($this->_addressData->getAddressCode() === $address->getEccErpAddressCode()) {
                $addressId = $address->getId();
            }
        }

        if ($this->_addressData->getAddressCode() === '') {
            $options[] = array(
                'value' => '',
                'label' => '',
                'params' => array(
                    'address_data' => $this->_addressData->getData(),
                    'id' => 'custom_address_selected',
                    'selected' => 'selected',
                    'full_country' => $this->getCountry(),
                    'data-address' => htmlentities(json_encode($this->_addressData->getData()))
                )
            );
        }
        $select = $this->getLayout()->createBlock('\Magento\Framework\View\Element\Html\Select')
            ->setName($this->getAddressType() . '_address_id')
            ->setId($this->getAddressType() . '-address-select')
            ->setClass('address-select')
            ->setValue($addressId)
            ->setOptions($options);

        if ($this->canAddNew()) {
            $select->addOption('', __('New Address'));
        }
        $html = $select->getHtml();

        return $html;
    }

    public function setAddressFromCustomerAddress($data)
    {
        /* @var $data \Magento\Customer\Model\Address */
        $this->_addressData = $this->dataObjectFactory->create(
            [
                'data' => array(
                    'name' => $data->getName(),
                    'company' => $data->getCompany(),
                    'address1' => $data->getStreet()[0],
                    'address2' => isset($data->getStreet()[1]) ? $data->getStreet()[1] : '',
                    'address3' => isset($data->getStreet()[2]) ? $data->getStreet()[2] : '',
                    'city' => $data->getCity(),
                    'county' => $data->getCounty() ?: $data->getRegionCode(),
                    'country' => $data->getCountry(),
                    'postcode' => $data->getPostcode(),
                    'email' => $data->getEccEmail(),
                    'telephone_number' => $data->getTelephone(),
                    'fax' => $data->getFax(),
                    'address_code' => $data->getEccErpAddressCode(),
                    'instructions' => $data->getEccInstructions()
                )
            ]
        );

        return $this;
    }

    private function canAddNew()
    {
        $helper = $this->commonHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        return $helper->customerAddressPermissionCheck('create');
    }

    public function isErpAddress()
    {
        return $this->_addressData->getAddressCode() !== '';
    }

    public function getAddressesFormHtml()
    {
        $type = 'delivery';

        $form = $this->getLayout()->createBlock('\Epicor\Customerconnect\Block\Customer\Editableaddress')
            ->setAddressType($type)
            ->setFieldnamePrefix($type . '_address[')
            ->setFieldnameSuffix(']')
            ->setShowAddressCode(false)
            ->setAddressData($this->dataObjectFactory->create(['data' => $this->_addressData->getData()]))->toHtml();

        return $form;
    }

    public function setAddressData($data)
    {
        $this->_addressData = $data;
        return $this;
    }

    public function setShowAddressCode($show)
    {
        $this->_showAddressCode = $show;
        return $this;
    }

    public function getShowAddressCode()
    {
        return $this->_showAddressCode;
    }

    public function displayEmail()
    {
        return $this->scopeConfig->isSetFlag('customer/address/display_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function displayMobilePhone()
    {
        return $this->scopeConfig->isSetFlag('customer/address/display_mobile_phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function displayInstructions()
    {
        return $this->scopeConfig->isSetFlag('customer/address/display_instructions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    //M1 > M2 Translation Begin (Rule 56)

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getAddressData()
    {
        return $this->_addressData;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $path
     * @return bool
     */
    public function getConfigFlag($path)
    {
        return $this->_scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return \Epicor\Customerconnect\Helper\Data
     */
    public function getCustomerconnectHelper()
    {
        return $this->customerconnectHelper;
    }

    /**
     * @return \Magento\Directory\Helper\Data
     */
    public function getDirectoryHelper()
    {
        return $this->directoryHelper;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return \Epicor\Common\Helper\Data
     */
    public function getCommonHelper()
    {
        return $this->commonHelper;
    }

    //M1 > M2 Translation End

    public function getDealerAccount()
    {
        $helper = $this->commonHelper;
        $dataMode = $helper->getDealerMode();
        return $dataMode;
    }


    public function checkDealerMasterShopper()
    {
        $customerData = $this->getCustomerSession()->getCustomer();
        $masterShopper = $customerData->getEccMasterShopper();
        $disabled = '';
        if (!$masterShopper) {
            $disabled = 'onclick="return false;"; style="opacity: 0.3;filter: alpha(opacity=40);"';
        }
        return $disabled;
    }

    /**
     * get Default info to create a contact
     *
     * return string
     */
    public function getNewContactInfo()
    {
        $customer = $this->getCustomerSession()->getCustomer();
        $erpAccountId = ($customer->getEccErpaccountId()) ? $customer->getEccErpaccountId() : $customer->getEccSupplierErpaccountId();
        $jsonArray = json_encode(
            [
                'ecc_access_roles' => $this->eccAccessRoles->getAccessRoles($customer->getId(), $erpAccountId),
                'ecc_access_rights' => $customer->getEccAccessRights(),
            ]
        );
        return htmlspecialchars($jsonArray);
    }

    /**
     * check if CUAD has returned an address value
     */
    public function getAddressSuppliedInCuad()
    {
        return $this->_addressData ? true : false;
    }

    /**
     * Check if Assign Master Shopper access is allowed
     *
     * @return bool
     */
    public function assignMasterShopperAllowed()
    {
        return $this->isAccessAllowed("Epicor_Customerconnect::customerconnect_account_information_contacts_assign_master_shopper");
    }
}

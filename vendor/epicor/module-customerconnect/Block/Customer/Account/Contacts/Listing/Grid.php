<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Account\Contacts\Listing;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{


    const FRONTEND_RESOURCE_UPDATE = 'Epicor_Customerconnect::customerconnect_account_information_contacts_update';
    const FRONTEND_RESOURCE_DELETE = 'Epicor_Customerconnect::customerconnect_account_information_contacts_delete';
    const FRONTEND_RESOURCE_SYNC = 'Epicor_Customerconnect::customerconnect_account_information_contacts_sync_contact';
    private $_allowEdit;
    private $_allowDelete;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->registry = $registry;
        $this->commHelper = $commHelper;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->storeManager = $context->getStoreManager();
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $helper = $this->commonAccessHelper;

        $this->_allowEdit = $helper->customerHasAccess('Epicor_Customerconnect', 'Account', 'saveContact', '', 'Access');
        $this->_allowDelete = $helper->customerHasAccess('Epicor_Customerconnect', 'Account', 'deleteContact', '', 'Access');
        if (!$this->customerconnectHelper->checkMsgAvailable('CUAU')) {
            $this->_allowEdit = false;
            $this->_allowDelete = false;
        }
        if ($this->_allowEdit && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_UPDATE)) {
            $this->setRowClickCallback('editContact');
        }

        $this->setId('customer_account_contacts_list');
        $this->setIdColumn('id');
        $this->setDefaultSort('name');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setMessageBase('customerconnect');
        $this->setCustomColumns($this->_getColumns());
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        //      $this->setRowUrlValue('*/*/editContact');

        $details = $this->registry->registry('customer_connect_account_details');

        if ($details) {
            $helper = $this->commHelper;
            $customerhelper = $this->customerconnectHelper;
            $contacts = $details->getVarienDataArrayFromPath('contacts/contact');
//            $contactsFound = array();
            $contactEmails = array();
            foreach ($contacts as $x => $contact) {
                if ($contact->getLoginId()) {
                    $contact->setSource($customerhelper::SYNC_OPTION_ECC_ERP);
                } else {
                    $contact->setSource($customerhelper::SYNC_OPTION_ONLY_ERP);
                }
                $contactEmails[$x] = $contact->getEmailAddress();
                $customer = $this->customerCustomerFactory->create();
                //M1 > M2 Translation Begin (Rule p2-6.5)
                //$customer->setWebsiteId(Mage::app()->getDefaultStoreView()->getWebsiteId());
                $customer->setWebsiteId($this->storeManager->getDefaultStoreView()->getWebsiteId());
                //M1 > M2 Translation End
                /* @var $customer Epicor_Comm_Model_Customer */

                if ($customer->loadByEmail($contact->getEmailAddress())) {
                    $contact->setMasterShopper($customer->getEccMasterShopper() ? 'y' : 'n');
                }
                $name = $contact->getName();
                $nameParts = explode(' ', $name, 2);
                if (count($nameParts) == 2) {
                    $contact->setFirstname($nameParts[0]);
                    $contact->setLastname($nameParts[1]);
                } else {
                    $contact->setFirstname($name);
                }
            }

            $erp_group = $helper->getErpAccountInfo();

            $customers = $erp_group->getCustomers()
                ->addAttributeToFilter('website_id', $this->storeManager->getWebsite()->getId())
                ->addAttributeToSelect('ecc_master_shopper')
                ->addAttributeToSelect('ecc_login_mode_type')
                ->addAttributeToSelect('ecc_is_toggle_allowed')->getItems();

            $customerEmails = array();
            foreach ($customers as $key => $customer) {
                /* @var $customer \Magento\Customer\Model\Customer */
                if (!in_array($customer->getEmail(), $customerEmails)) {
                    $customerEmails[] = $customer->getEmail();

                    $login_mode_type = $customer->getEccLoginModeType();
                    $istoggleAllowed = $customer->getEccIsToggleAllowed();
                    if (isset($login_mode_type) && trim($login_mode_type) != '') {
                        $modeType = $login_mode_type;
                    } else {
                        $modeType = "2";
                    }
                    if (isset($istoggleAllowed) && trim($istoggleAllowed) != '') {
                        $toggleType = $istoggleAllowed;
                    } else {
                        $toggleType = "2";
                    }

                    if (!in_array($customer->getEmail(), $contactEmails)) {
                        $eccContact = $this->dataObjectFactory->create();
                        $eccContact->setContact_code($customer->getEccContactCode());
                        $eccContact->setName($customer->getName());
                        $eccContact->setFirstname($customer->getFirstname());
                        $eccContact->setLastname($customer->getLastname());
                        $eccContact->setFunction($customer->getEccFunction());
                        $eccContact->setTelephone_number($customer->getEccTelephoneNumber());
                        $eccContact->setFax_number($customer->getEccFaxNumber());
                        $eccContact->setEmailAddress($customer->getEmail());
                        $eccContact->setLoginId($customer->getEccErpLoginId());
                        $eccContact->setSource($customerhelper::SYNC_OPTION_ONLY_ECC); //"Only in ECC"
                        $eccContact->setMasterShopper($customer->getEccMasterShopper() ? 'y' : 'n');
                        $eccContact->setLoginModeType($modeType);
                        $eccContact->setIsToggleAllowed($toggleType);
                        $contacts[] = $eccContact;
                    } else {
                        $contactKey = array_search($customer->getEmail(), $contactEmails);
                        $contact = $contacts[$contactKey];
                        $customerVal = $customer->getEccMasterShopper() ? 'y' : 'n';
                        $contact->setMasterShopper($customerVal);
                        $contact->setLoginModeType($modeType);
                        $contact->setIsToggleAllowed($toggleType);
                    }
                }
            }

            $this->setCustomData($contacts);
        } else {
            $this->setCustomColumns(array());
            $this->setCustomData(array());
            $this->setFilterVisibility(false);
            $this->setPagerVisibility(false);
        }
    }

    protected function _getColumns()
    {

        $columns = array(
            'name' => array(
                'header' => __('Name'),
                'align' => 'left',
                'index' => 'name',
                'width' => '100px',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Account\Contacts\Renderer\Contacts',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'function' => array(
                'header' => __('Function'),
                'align' => 'left',
                'index' => 'function',
                'width' => '50px',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'telephone_number' => array(
                'header' => __('Telephone Number'),
                'align' => 'left',
                'index' => 'telephone_number',
                'width' => '50px',
                'type' => 'phone',
                'condition' => 'LIKE'
            ),
            'fax_number' => array(
                'header' => __('Fax Number'),
                'align' => 'left',
                'index' => 'fax_number',
                'width' => '50px',
                'type' => 'phone',
                'condition' => 'LIKE'
            ),
            'email_address' => array(
                'header' => __('Email Address'),
                'align' => 'left',
                'index' => 'email_address',
                'width' => '100px',
                'type' => 'email',
                'condition' => 'LIKE'
            ),
            'login_id' => array(
                'header' => __('Web Enabled'),
                'align' => 'center',
                'index' => 'login_id',
                'width' => '75px',
                'type' => 'options',
                'options' => array(
                    'yes' => 'Yes',
                    'no' => 'No'
                ),
                'tick_mode' => 'content',
                'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Tickcross',
                'filter_condition_callback' => array($this, 'filterWebEnabledCallback')
            ),
            'master_shopper' => array(
                'header' => __('Master Shopper'),
                'align' => 'center',
                'index' => 'master_shopper',
                'width' => '75px',
                'type' => 'options',
                'options' => array(
                    'y' => __('Yes'),
                    'n' => __('No'),
                    // 'wyen' => Mage::helper('epicor_comm')->__('Web-Yes ERP-No'),
                    //'wney' => Mage::helper('epicor_comm')->__('Web-No ERP-Yes'),
                ),
                'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Mastershopper',
            ),
            'source' => array(
                'header' => __('ERP Web Enabled'),
                'align' => 'left',
                'index' => 'source',
                'width' => '100px',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Account\Contacts\Renderer\Contactsource',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
        );


        if (($this->_allowEdit || $this->_allowDelete) &&
            (
                $this->_isAccessAllowed(static::FRONTEND_RESOURCE_DELETE) ||
                $this->_isAccessAllowed(static::FRONTEND_RESOURCE_UPDATE)
            )
        ) {
            $columns['action'] = array(
                'header' => __('Action'),
                'width' => '25px',
                'type' => 'action',
                'action' => array(),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Account\Contacts\Renderer\Contactsync',
            );

            if ($this->_allowEdit && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_UPDATE)) {
                $columns['action']['actions'][] = array(
                    'caption' => __('Edit'),
                    'url' => "javascript:;",
                );
            }

            if ($this->_allowDelete && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_DELETE)) {
                $columns['action']['actions'][] = array(
                    'caption' => __('Delete'),
                    'confirm' => __('Are you sure you want to delete this contact?  This action cannot be undone.'),
                    'url' => "javascript:void(0);",
                );
            }
        }
        if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_SYNC)) {
            $columns['action']['actions'][] = array(
                'caption' => __('Sync Contact'),
                'url' => "javascript:;",
            );
        }
        return $columns;
    }

    /**
     * Filters the web enabled column
     *
     * @param \Epicor\Common\Model\Message\Collection $collection
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     */
    protected function filterWebEnabledCallback($collection, $column)
    {
        $filterValue = $column->getFilter()->getValue();
        if ($filterValue == 'yes') {
            $collection->addFilter('login_id', array('neq' => ''));
        } else {
            $collection->addFilter('login_id', array('eq' => ''));
        }
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/grid/contactssearch');
    }

}

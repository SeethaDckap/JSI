<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller;

use Magento\Customer\Controller\AccountInterface;

/**
 * List frontend actions
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
abstract class Lists extends \Epicor\Customerconnect\Controller\Generic implements AccountInterface
{

    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_lists_read';
    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTimeDateTime;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    protected $listsFrontendRestrictedHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        
        $this->backendJsHelper = $backendJsHelper;
        $this->commHelper = $commHelper;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->generic = $generic;
        $this->listsHelper = $listsHelper;
        $this->customerSession = $customerSession;
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->dateTimeTimezone = $timezone;
        
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    /**
     * Checks if Customers Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @param array $data
     */
    protected function processCustomersSave($list, $data)
    {
        if (isset($data['links']['customers'])) {
            $this->saveCustomers($list, $data);
        }
    }

    /**
     * Save Customers Information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     * @return void
     */
    protected function saveCustomers(&$list, $data)
    {
        $customers = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['customers']));
        $list->removeCustomers($list->getCustomers());
        $list->addCustomers($customers);
    }

    /**
     * Checks if Products Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @param array $data
     */
    protected function processProductsSave($list, $data)
    {
        $products = $this->getRequest()->getParam('selected_products');
        if (!is_null($products)) {
            $this->saveProducts($list, $data);
        }
    }

    /**
     * Save Products Information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     * @return void
     */
    protected function saveProducts(&$list, $data)
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $products = array_keys($helper->decodeGridSerializedInput($data['links']['products']));
        $list->removeProducts($list->getProducts());
        $list->addProducts($products);
    }
/**
     * Checks if Settings Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     */
    protected function processSettingsSave($list, $data)
    {
        $settings = isset($data['settings']) ? $data['settings'] : array();

        if (isset($data['exclude_selected_products'])) {
            $settings[] = 'E';
        }

        $list->setSettings($settings);
    }

    protected function processErpOverrideSave($list, $data)
    {
        $overrides = isset($data['erp_override']) ? $data['erp_override'] : array();
        $list->setErpOverride($overrides);
    }
/**
     * Saves details for the list
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     */
    protected function processDetailsSave($list, $data)
    {
        $list->setTitle($data['title']);
        $list->setNotes($data['notes']);
        $list->setPriority($data['priority']);
        $list->setActive(isset($data['active']) ? 1 : 0);
        if ($list->isObjectNew()) {
            $list->setErpCode();
            $list->setType($data['type']);
        } else {
            $this->processErpOverrideSave($list, $data);
        }
        if (empty($data['start_date']) == false) {
            $time = implode(':', $data['start_time']);
            $dateTime = $data['start_date'] . ' ' . $time;
            $list->setStartDate($this->dateTimeTimezone->convertConfigTimeToUtc($dateTime));
        } else {
            $list->setStartDate(false);
        }
        if (empty($data['end_date']) == false) {
            $time = implode(':', $data['end_time']);
            $dateTime = $data['end_date'] . ' ' . $time;
            $list->setEndDate($this->dateTimeTimezone->convertConfigTimeToUtc($dateTime));
        } else {
            $list->setEndDate(false);
        }
        // don't know why erp code is cleared out at top of this section , it is needed if erp_code has a value
        // if save new cart to list, ensure the erp code is set to that previously generated
        if(isset($data['erp_code'])){
            $list->setErpCode($data['erp_code']);
        }


        $this->processSettingsSave($list, $data);
    }

    /**
     * Save ERP Accounts Information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     * @return void
     */
    protected function saveERPAccounts(&$list, $data)
    {

        $list->setErpAccountLinkType('E');
        if ($list->getErpAccountLinkType() == 'E') {
            $list->addErpAccounts($erpaccounts);
        }
    }
    
    protected function delete($id, $mass = false)
    {
        $model = $this->listsListModelFactory->create();
        /* @var $list Epicor_Lists_Model_ListModel */
        $session = $this->generic;
        $helper = $this->listsHelper;
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $erpCode = $model->getErpCode();
                $ownerId = $model->getOwnerId();
                $customerSession = $this->customerSession->getCustomer();
                $checkMasterErp = $model->isValidEditForErpAccount($customerSession, $id);
                $checkCustomer = $model->isValidEditForCustomers($customerSession, $id, $ownerId);
                //$defaultCheck   = $model->isValidForCustomer(Mage::getSingleton('customer/session')->getCustomer());
                if ((!$checkMasterErp) || (!$checkCustomer)) {
                    $this->messageManager->addError(__('Could not delete List %d', $erpCode));
                } else {
                    if ($model->delete()) {
                        if (!$mass) {
                            $this->messageManager->addSuccess(__('List Deleted'));
                        } else {
                            return $erpCode;
                        }
                    } else {
                        $this->messageManager->addError(__('Could not delete List ' . $erpCode));
                    }
                }
            }
        }
    }
/**
     * Loads List
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    protected function loadEntity()
    {
        $id = base64_decode($this->getRequest()->getParam('id', null));
        $list = $this->listsListModelFactory->create()->load($id);
        /* @var $list Epicor_Lists_Model_ListModel */
        //Mage::register('list', $list);
        return $list;
    }
protected function sendAjaxResponse($values, $addressId)
    {
        $frontendHelper = $this->listsFrontendRestrictedHelper;
        /* @var $frontendHelper Epicor_Lists_Helper_Frontend_Restricted */

        $result = array(
            'type' => 'success',
            'values' => !empty($values) ? $values : array(),
            'addressid' => $addressId,
            'details' => $frontendHelper->getShippingAddress($addressId)
        );

        //Mage::App()->getResponse()->setHeader('Content-type', 'application/json');
        //Mage::App()->getResponse()->setBody(json_encode($result));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }
    public function getListModelFactory(){
        return $this->listsListModelFactory;
    }
}
